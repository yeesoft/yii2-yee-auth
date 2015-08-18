<?php
/**
 * @var $this yii\web\View
 * @var $user yeesoft\models\User
 */
use yii\helpers\Html;

?>
<?php
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['/auth/default/confirm-email-receive', 'token' => $user->confirmation_token]);
?>

    Hello <?= Html::encode($user->username) ?>, follow this link to confirm your email:

<?= Html::a('Confirm E-mail', $resetLink) ?>