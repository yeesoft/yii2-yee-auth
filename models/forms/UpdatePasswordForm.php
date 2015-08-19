<?php

namespace yeesoft\auth\models\forms;

use yeesoft\models\User;
use yeesoft\Yee;
use Yii;
use yii\base\Model;

class UpdatePasswordForm extends Model
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $current_password;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $repeat_password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'repeat_password'], 'required'],
            [['password', 'repeat_password', 'current_password'], 'string', 'max' => 255],
            [['password', 'repeat_password', 'current_password'], 'trim'],
            ['repeat_password', 'compare', 'compareAttribute' => 'password'],
            ['current_password', 'required', 'except' => 'restoreViaEmail'],
            ['current_password', 'validateCurrentPassword', 'except' => 'restoreViaEmail'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'current_password' => Yee::t('back', 'Current password'),
            'password' => Yee::t('front', 'Password'),
            'repeat_password' => Yee::t('front', 'Repeat password'),
        ];
    }

    /**
     * Validates current password
     */
    public function validateCurrentPassword()
    {
        if (!Yii::$app->getModule('yee')->checkAttempts()) {
            $this->addError('current_password', Yee::t('back', 'Too many attempts'));
            return false;
        }

        if (!Yii::$app->security->validatePassword($this->current_password, $this->user->password_hash)) {
            $this->addError('current_password', Yee::t('back', "Wrong password"));
        }
    }

    /**
     * @param bool $performValidation
     *
     * @return bool
     */
    public function updatePassword($performValidation = true)
    {
        if ($performValidation AND !$this->validate()) {
            return false;
        }

        $this->user->password = $this->password;
        $this->user->removeConfirmationToken();
        return $this->user->save();
    }
}