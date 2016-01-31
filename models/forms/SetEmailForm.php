<?php

namespace yeesoft\auth\models\forms;

use yeesoft\models\User;
use Yii;
use yii\base\Model;

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
     * Check that there is no such E-mail in the system
     */
    public function validateEmailUnique()
    {

        if ($this->email) {
            $exists = User::findOne([
                'email' => $this->email,
            ]);

            if ($exists) {
                $this->addError('email', Yii::t('yee/auth', 'This E-mail already exists'));
            }
        }
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
        ];
    }

}