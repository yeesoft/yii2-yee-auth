<?php

use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\PasswordRecoveryForm $model
 */
$this->title = Yii::t('yee/auth', 'Reset Password');

$col12 = $this->context->module->gridColumns;
$col9 = (int) ($col12 * 3 / 4);
$col6 = (int) ($col12 / 2);
$col3 = (int) ($col12 / 4);
?>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert-alert-warning text-center">
        <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

    <div id="update-wrapper">
        <div class="row">
            <div class="col-md-<?= $col6 ?> col-md-offset-<?= $col3 ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= $this->title ?></h3>
                    </div>
                    <div class="panel-body">

                        <?php $form = ActiveForm::begin([
                            'id' => 'reset-form',
                            'options' => ['autocomplete' => 'off'],
                            'validateOnBlur' => false,
                        ]); ?>

                        <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>

                        <?= $form->field($model, 'captcha')->widget(Captcha::className(), [
                            'template' => '<div class="row"><div class="col-sm-' . $col3 . '">{image}</div><div class="col-sm-' . $col3 . '">{input}</div></div>',
                            'captchaAction' => ['/auth/captcha']
                        ]) ?>

                        <?= Html::submitButton(Yii::t('yee/auth', 'Reset'), ['class' => 'btn btn-lg btn-primary btn-block']) ?>

                        <?php ActiveForm::end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$css = <<<CSS
#update-wrapper {
	position: relative;
	top: 30%;
}
CSS;

$this->registerCss($css);
?>