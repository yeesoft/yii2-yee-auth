<?php

namespace yeesoft\auth\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yeesoft\models\User;
use yeesoft\auth\AuthModule;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $repeat_password;
    public $captcha;
    public $terms = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['captcha', 'captcha', 'captchaAction' => '/auth/default/captcha'],
            [['username', 'email', 'password', 'repeat_password', 'captcha', 'terms'], 'required'],
            [['username', 'email', 'password', 'repeat_password'], 'trim'],
            [['email'], 'email'],
            [['terms'], 'boolean'],
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
            ['username', 'match', 'pattern' => Yii::$app->usernameRegexp, 'message' => Yii::t('yee/auth', 'The username should contain only Latin letters, numbers and the following characters: "-" and "_".')],
            ['username', 'match', 'not' => true, 'pattern' => Yii::$app->usernameBlackRegexp, 'message' => Yii::t('yee/auth', 'This username is not available. It contains not allowed characters or words.')],
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
            'username' => Yii::t('yee/auth', 'Login'),
            'email' => Yii::t('yee/auth', 'Email'),
            'password' => Yii::t('yee/auth', 'Password'),
            'repeat_password' => Yii::t('yee/auth', 'Repeat password'),
            'captcha' => Yii::t('yee/auth', 'Captcha'),
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

        if (Yii::$app->controller->module->enableEmailConfirmation) {
            $user->status = User::STATUS_INACTIVE;
            $user->generateConfirmationToken();
            // $user->save(false);

            if (!$this->sendConfirmationEmail($user)) {
                $this->addError('username', Yii::t('yee/auth', 'An error occurred while sending mail. Please try again.'));
            }
        }

        if (!$user->save()) {
            $this->addError('username', Yii::t('yee/auth', 'Username has already been taken. Please choose another username.'));
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
        /* @var $mailer \yii\swiftmailer\Mailer */
        $mailer = Yii::$app->mailer;
        $view = Yii::$app->controller->module->emailTemplates[AuthModule::EMAIL_SIGNUP_CONFIRMATION];
        $subject = Yii::t('yee/auth', '[{sitename}] Please verify your email address.', ['sitename' => Yii::$app->name]);

        return $mailer->compose($view, ['user' => $user])
            ->setFrom(Yii::$app->emailSender)
            ->setTo($user->email)
            ->setSubject($subject)
            ->send();
    }

}