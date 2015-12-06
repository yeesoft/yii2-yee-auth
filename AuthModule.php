<?php
/**
 * @link http://www.yee-soft.com/
 * @copyright Copyright (c) 2015 Taras Makitra
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace yeesoft\auth;

/**
 * Auth Module For Yee CMS
 *
 * @author Taras Makitra <makitrataras@gmail.com>
 */
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

}