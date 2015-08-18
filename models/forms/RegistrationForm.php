<?php

namespace yeesoft\auth\models\forms;

use yeesoft\models\User;
use yeesoft\Yee;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

class RegistrationForm extends Model
{
    public $username;
    public $password;
    public $repeat_password;
    public $captcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['captcha', 'captcha', 'captchaAction' => '/auth/default/captcha'],
            [['username', 'password', 'repeat_password', 'captcha'], 'required'],
            [['username', 'password', 'repeat_password'], 'trim'],
            ['username', 'unique',
                'targetClass' => 'yeesoft\models\User',
                'targetAttribute' => 'username',
            ],
            ['username', 'purgeXSS'],
            ['password', 'string', 'max' => 255],
            ['repeat_password', 'compare', 'compareAttribute' => 'password'],
        ];

        if (Yii::$app->getModule('yee')->useEmailAsLogin) {
            $rules[] = ['username', 'email'];
        } else {
            $rules[] = ['username', 'string', 'max' => 50];

            $rules[] = ['username', 'match', 'pattern' => Yii::$app->getModule('yee')->registrationRegexp];
            $rules[] = ['username', 'match', 'not' => true, 'pattern' => Yii::$app->getModule('yee')->registrationBlackRegexp];
        }

        return $rules;
    }

    /**
     * Remove possible XSS stuff
     *
     * @param $attribute
     */
    public function purgeXSS($attribute)
    {
        $this->$attribute = Html::encode($this->$attribute);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::$app->getModule('yee')->useEmailAsLogin ? 'E-mail' : Yee::t('front', 'Login'),
            'password' => Yee::t('front', 'Password'),
            'repeat_password' => Yee::t('front', 'Repeat password'),
            'captcha' => Yee::t('front', 'Captcha'),
        ];
    }

    /**
     * @param bool $performValidation
     *
     * @return bool|User
     */
    public function registerUser($performValidation = true)
    {
        if ($performValidation AND !$this->validate()) {
            return false;
        }

        $user = new User();
        $user->password = $this->password;

        if (Yii::$app->getModule('yee')->useEmailAsLogin) {
            $user->email = $this->username;

            // If email confirmation required then we save user with "inactive" status
            // and without username (username will be filled with email value after confirmation)
            if (Yii::$app->getModule('yee')->emailConfirmationRequired) {
                $user->status = User::STATUS_INACTIVE;
                $user->generateConfirmationToken();
                $user->save(false);

                $this->saveProfile($user);

                if ($this->sendConfirmationEmail($user)) {
                    return $user;
                } else {
                    $this->addError('username', Yee::t('front', 'Could not send confirmation email'));
                }
            } else {
                $user->username = $this->username;
            }
        } else {
            $user->username = $this->username;
        }


        if ($user->save()) {
            $this->saveProfile($user);

            return $user;
        } else {
            $this->addError('username', Yee::t('front', 'Login has been taken'));
        }
    }

    /**
     * Implement your own logic if you have user profile and save some there after registration
     *
     * @param User $user
     */
    protected function saveProfile($user)
    {

    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function sendConfirmationEmail($user)
    {
        return Yii::$app->mailer->compose(Yii::$app->getModule('yee')->mailerOptions['registrationFormViewFile'],
            ['user' => $user])
            ->setFrom(Yii::$app->getModule('yee')->mailerOptions['from'])
            ->setTo($user->email)
            ->setSubject(Yee::t('front', 'E-mail confirmation for') . ' ' . Yii::$app->name)
            ->send();
    }

    /**
     * Check received confirmation token and if user found - activate it, set username, roles and log him in
     *
     * @param string $token
     *
     * @return bool|User
     */
    public function checkConfirmationToken($token)
    {
        $user = User::findInactiveByConfirmationToken($token);

        if ($user) {
            $user->username = $user->email;
            $user->status = User::STATUS_ACTIVE;
            $user->email_confirmed = 1;
            $user->removeConfirmationToken();
            $user->save(false);

            $roles = (array)Yii::$app->getModule('yee')->rolesAfterRegistration;

            foreach ($roles as $role) {
                User::assignRole($user->id, $role);
            }

            Yii::$app->user->login($user);

            return $user;
        }

        return false;
    }
}