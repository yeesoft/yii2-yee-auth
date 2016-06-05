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
    const VERSION = '0.1.0';
    const PARAMS_SESSION_ID = 'authUserParams';

    /**
     * Bootstrap grid columns count.
     *
     * @var int
     */
    public $gridColumns = 12;
    
    /**
     * Controller namespace
     *
     * @var string
     */
    public $controllerNamespace = 'yeesoft\auth\controllers';

    public static function getAuthAttributes()
    {
        return [
            'google' => [
                'email' => 'emails.0.value',
                'username' => 'displayName',
            ],
            'facebook' => [
                'email' => 'email',
                'username' => 'name',
            ],
            'twitter' => [
                'username' => 'screen_name',
            ],
            'github' => [
                'email' => 'email',
                'username' => 'name',
            ],
            'linkedin' => [
                'email' => 'email',
                'username' => 'first-name',
            ],
            'vkontakte' => [
                'username' => 'first_name',
            ],
            'odnoklassniki' => [
                'username' => 'name',
            ],
        ];
    }
}