<?php

namespace yeesoft\auth;

class AuthModule extends \yii\base\Module
{
    /**
     * Version number of the module.
     */
    const VERSION = '0.1-a';

    /**
     * Controller namespace
     *
     * @var string
     */
    public $controllerNamespace = 'yeesoft\auth\controllers';

    /**
     * @p
     */
    public function init()
    {
        parent::init();
    }
}