<?php

namespace yeesoft\auth\models\forms;

use Yii;
use yii\base\Model;
use yeesoft\models\User;
use yeesoft\auth\AuthModule;

class ResetPasswordForm extends Model
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $captcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['captcha', 'captcha', 'captchaAction' => '/auth/default/captcha'],
            [['email', 'captcha'], 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'validateEmailConfirmedAndUserActive'],
        ];
    }

    /**
     * @return bool
     */
    public function validateEmailConfirmedAndUserActive()
    {
        if (!Yii::$app->checkAttempts()) {
            $this->addError('email', Yii::t('yee/auth', 'Too many attempts. Please try again later.'));
            return false;
        }

        $user = User::findOne([
            'email' => $this->email,
            'email_confirmed' => 1,
            'status' => User::STATUS_ACTIVE,
        ]);

        if ($user) {
            $this->user = $user;
        } else {
            $this->addError('email', Yii::t('yee/auth', 'Your account is not active.'));
        }
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'captcha' => Yii::t('yee/auth', 'Captcha'),
        ];
    }

    /**
     * @param bool $performValidation
     *
     * @return bool
     */
    public function sendEmail($performValidation = true)
    {
        if ($performValidation AND !$this->validate()) {
            return false;
        }
       
        $this->user->generateConfirmationToken();
        $this->user->save(false);

        $subject = Yii::t('yee/auth', '[{sitename}] Please reset your password', ['sitename' => Yii::$app->name]);
        $view = Yii::$app->controller->module->emailTemplates[AuthModule::EMAIL_PASSWORD_RESET];
        
        return Yii::$app->mailer->compose($view, ['user' => $this->user])
            ->setFrom(Yii::$app->emailSender)
            ->setTo($this->email)
            ->setSubject($subject)
            ->send();
    }
}