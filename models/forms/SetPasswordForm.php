<?php

namespace yeesoft\auth\models\forms;

use Yii;
use yii\base\Model;

class SetPasswordForm extends Model
{

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
            [['password', 'repeat_password'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
            [['password', 'repeat_password'], 'trim'],
            ['repeat_password', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => Yii::t('yee/auth', 'Password'),
            'repeat_password' => Yii::t('yee/auth', 'Repeat password'),
        ];
    }

}