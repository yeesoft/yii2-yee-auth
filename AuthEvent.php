<?php

namespace yeesoft\auth;

use yii\base\Event;

class AuthEvent extends Event
{
    const BEFORE_REGISTRATION = 'beforeRegistration';
    const AFTER_REGISTRATION = 'afterRegistration';
    const BEFORE_PASSWORD_RECOVERY_REQUEST = 'beforePasswordRecoveryRequest';
    const AFTER_PASSWORD_RECOVERY_REQUEST = 'afterPasswordRecoveryRequest';
    const BEFORE_PASSWORD_RECOVERY_COMPLETE = 'beforePasswordRecoveryComplete';
    const AFTER_PASSWORD_RECOVERY_COMPLETE = 'afterPasswordRecoveryComplete';

    /**
     * @var User
     */
    public $user;

    /**
     * @var RegistrationForm|PasswordRecoveryForm|ConfirmEmailForm
     */
    public $model;

    /**
     * Determine if script should continue after this event
     *
     * @var boolean
     */
    public $isValid = true;

}