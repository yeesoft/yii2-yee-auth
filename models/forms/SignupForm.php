<?php

namespace yeesoft\auth\models\forms;

use yeesoft\models\User;
use yeesoft\Yee;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

class SignupForm extends Model
{
    public $username;
    public $email;
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
            [['username', 'email', 'password', 'repeat_password', 'captcha'], 'required'],
            [['username', 'email', 'password', 'repeat_password'], 'trim'],
            [['email'], 'email'],
            ['username', 'unique',
                'targetClass' => 'yeesoft\models\User',
                'targetAttribute' => 'username',
            ],
            ['email', 'unique',
                'targetClass' => 'yeesoft\models\User',
                'targetAttribute' => 'email',
            ],
            ['username', 'purgeXSS'],
            ['username', 'string', 'max' => 50],
            ['username', 'match', 'pattern' => Yii::$app->getModule('yee')->usernameRegexp],
            ['username', 'match', 'not' => true, 'pattern' => Yii::$app->getModule('yee')->usernameBlackRegexp],
            ['password', 'string', 'max' => 255],
            ['repeat_password', 'compare', 'compareAttribute' => 'password'],
        ];

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
            'username' => Yee::t('front', 'Login'),
            'email' => Yee::t('front', 'E-mail'),
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
    public function signup($performValidation = true)
    {
        if ($performValidation AND !$this->validate()) {
            return false;
        }

        $user = new User();
        $user->password = $this->password;
        $user->username = $this->username;
        $user->email = $this->email;

        if (Yii::$app->getModule('yee')->emailConfirmationRequired) {
            $user->status = User::STATUS_INACTIVE;
            $user->generateConfirmationToken();

            if (!$this->sendConfirmationEmail($user)) {
                $this->addError('username', Yee::t('front', 'Could not send confirmation email'));
            }
        }

        if (!$user->save()) {
            $this->addError('username', Yee::t('front', 'Login has been taken'));
        } else {
            return $user;
        }

        return FALSE;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function sendConfirmationEmail($user)
    {
        return Yii::$app->mailer
            ->compose(Yii::$app->getModule('yee')->mailerOptions['signup-confirmation'], ['user' => $user])
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

            $user->assignRoles(Yii::$app->getModule('yee')->rolesAfterRegistration);

            Yii::$app->user->login($user);

            return $user;
        }

        return false;
    }
}