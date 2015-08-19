<?php
/**
 * @var $this yii\web\View
 * @var $user yeesoft\models\User
 */
use yii\helpers\Html;

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/auth/default/reset-password-request', 'token' => $user->confirmation_token]);
?>

    Hello <?= Html::encode($user->username) ?>, follow the link below to reset your password: <?= $resetLink ?>