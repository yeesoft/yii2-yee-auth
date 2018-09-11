<?php

/**
 * @link http://www.yee-soft.com/
 * @copyright Copyright (c) 2015 Taras Makitra
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace yeesoft\auth;

use yii\base\Module;

/**
 * Authorization Module For Yee CMS
 *
 * @author Taras Makitra <makitrataras@gmail.com>
 */
class AuthModule extends Module
{

    /**
     * User attributes session key. Used to complete the registration of the user 
     * when registering via OAuth.
     */
    const USER_ATTRIBUTES_SESSION_KEY = 'authUserParams';

    /**
     * Key for sign up confirmation email.
     */
    const EMAIL_SIGNUP_CONFIRMATION = 'signup-confirmation';

    /**
     * Key for password reset email.
     */
    const EMAIL_PASSWORD_RESET = 'password-reset';

    /**
     * @var string controller namespace.
     */
    public $controllerNamespace = 'yeesoft\auth\controllers';

    /**
     * @var bool whether to enable new user registration functionality.
     * Defaults to true.
     */
    public $enableRegistration = true;

    /**
     * @var bool whether to enable oAuth functionality.
     * Defaults to true.
     */
    public $enableOAuth = true;

    /**
     * @var bool whether to enable profile functionality.
     * Defaults to true.
     */
    public $enableProfile = true;

    /**
     * @var bool whether to enable password reset functionality.
     * Defaults to true.
     */
    public $enablePasswordReset = true;

    /**
     * @var bool whether is it required to confirm email after registration.
     * The account will be activated after confirmation by the user.
     */
    public $enableEmailConfirmation = true;
    
    /**
     * @var array|string|bool link to the terms and conditions page. 
     * User will be asked to confirm terms and conditions before registration.
     * If this value is set to false, the confirmation will be hidden.
     * Defaults to false. 
     */
    public $termsAndConditions = false;

    /**
     * @var array list of classes for parsing user authorization data.
     */
    public $attributeParsers = [
        'google' => 'yeesoft\auth\parsers\GoogleAttributesParser',
        'facebook' => 'yeesoft\auth\parsers\FacebookAttributesParser',
    ];

    /**
     * @var int default duration in seconds before the confirmation token will expire.
     */
    public $confirmationTokenLifetime = 3600;

    /**
     * @var string layout file for authorization module.
     */
    public $layout = '@vendor/yeesoft/yii2-yee-auth/views/layouts/main';

    /**
     * @var string|boolean logo view file. If false, logo will be removed from all pages.
     */
    public $logo = '@vendor/yeesoft/yii2-yee-auth/views/logo';
    
    /**
     * @var string profile pages layout.
     */
    public $profileLayout;
    
    /**
     * @var array email templates.
     */
    public $emailTemplates = [
        self::EMAIL_SIGNUP_CONFIRMATION => '/mail/signup-confirmation',
        self::EMAIL_PASSWORD_RESET => '/mail/password-reset',
    ];

}
