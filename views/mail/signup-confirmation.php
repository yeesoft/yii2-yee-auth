<?php
/* @var $user yeesoft\models\User */
/* @var $this yii\web\View */

use yii\helpers\Html;

$confirmation = Yii::$app->urlManager->createAbsoluteUrl(['/auth/default/confirm-email', 'token' => $user->confirmation_token]);
?>

<p>
    Hello <?= $user->username ?>!
</p>

<p>
    Welcome to <?= Yii::$app->name ?>. Follow this link to confirm your email address and activate account: 
</p>

<p>
    <?= Html::a('Confirm Email Address', $confirmation) ?>
</p>

<p>
    Link not working? Paste the following link into your browser: <br/>
    <?= $confirmation ?>
</p>

<p>
    You're receiving this email because you recently created a new account. If this wasn't you, please ignore this email.
</p>