<?php

use yeesoft\db\SourceMessagesMigration;

class m151126_061233_i18n_yee_auth_source extends SourceMessagesMigration
{

    public function getCategory()
    {
        return 'yee/auth';
    }

    public function getMessages()
    {
        return [
            'Are you sure you want to delete your profile picture?' => 1,
            'Are you sure you want to unlink this authorization?' => 1,
            'Authentication error occurred.' => 1,
            'Authorization' => 1,
            'Authorized Services' => 1,
            'Captcha' => 1,
            'Change profile picture' => 1,
            'Check your E-mail for further instructions' => 1,
            'Check your e-mail {email} for instructions to activate account' => 1,
            'Click to connect with service' => 1,
            'Click to unlink service' => 1,
            'Confirm E-mail' => 1,
            'Confirm' => 1,
            'Could not send confirmation email' => 1,
            'Current password' => 1,
            'E-mail confirmation for' => 1,
            'E-mail confirmed' => 1,
            'E-mail is invalid' => 1,
            'E-mail with activation link has been sent to <b>{email}</b>. This link will expire in {minutes} min.' => 1,
            'E-mail' => 1,
            'Forgot password?' => 1,
            'Incorrect username or password' => 1,
            'Login has been taken' => 1,
            'Login' => 1,
            'Logout' => 1,
            'Non Authorized Services' => 1,
            'Password has been updated' => 1,
            'Password recovery' => 1,
            'Password reset for' => 1,
            'Password' => 1,
            'Registration - confirm your e-mail' => 1,
            'Registration' => 1,
            'Remember me' => 1,
            'Remove profile picture' => 1,
            'Repeat password' => 1,
            'Reset Password' => 1,
            'Reset' => 1,
            'Save Profile' => 1,
            'Save profile picture' => 1,
            'Set Password' => 1,
            'Set Username' => 1,
            'Signup' => 1,
            'This E-mail already exists' => 1,
            'Token not found. It may be expired' => 1,
            'Token not found. It may be expired. Try reset password once more' => 1,
            'Too many attempts' => 1,
            'Unable to send message for email provided' => 1,
            'Update Password' => 1,
            'User Profile' => 1,
            "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it." => 1,
            'The username should contain only Latin letters, numbers and the following characters: "-" and "_".' => 1,
            'Username contains not allowed characters or words.' => 1,
            'Wrong password' => 1,
            'You could not login from this IP' => 1,
        ];
    }
}