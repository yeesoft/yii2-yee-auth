<?php

namespace yeesoft\auth\models\forms;

use Yii;
use yii\base\Model;
use yeesoft\models\User;

class SetEmailForm extends Model
{

    /**
     * @var string
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'validateEmailUnique'],
        ];
    }

    /**
     * Check that there is no such email address in the system
     */
    public function validateEmailUnique()
    {

        if ($this->email) {
            $exists = User::findOne([
                'email' => $this->email,
            ]);

            if ($exists) {
                $this->addError('email', Yii::t('yee/auth', 'The email address you have entered is already registered.'));
            }
        }
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }

}