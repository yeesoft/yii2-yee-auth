<?php
/* @var $user yeesoft\models\User */
/* @var $this yii\web\View */

use yii\helpers\Html;

$reset = Yii::$app->urlManager->createAbsoluteUrl(['/auth/default/password-reset', 'token' => $user->confirmation_token]);
?>

<p>
    Hello <?= $user->username ?>!
</p>

<p>
    You can use the following link to reset your password:
</p>

<p>
    <?= Html::a('Reset Password', $reset) ?>
</p>

<p>
    If you don't use this link within one hour, it will expire.
</p>

<p>
    Link not working? Paste the following link into your browser: <br/>
    <?= $reset ?>
</p>

<p>
    You're receiving this email because you recently sent request to reset your password. If this wasn't you, please ignore this email.
</p>