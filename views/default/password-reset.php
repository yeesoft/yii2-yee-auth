<?php

use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\PasswordRecoveryForm $model
 */
$this->title = Yii::t('yee/auth', 'Please enter your email address and we will send you a link to reset your password.');
?>

<div class="login-box">
    <?php if ($this->context->module->logo): ?>
        <?= $this->render($this->context->module->logo) ?>
    <?php endif; ?>
    
    <div class="login-box-body">
        <p class="login-box-msg"><?= $this->title ?></p>

        <?php $form = ActiveForm::begin(['id' => 'reset-form', 'options' => ['autocomplete' => 'off'], 'validateOnBlur' => false]) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>

        <?=
        $form->field($model, 'captcha')->widget(Captcha::className(), [
            'template' => '<div class="row"><div class="col-sm-6">{image}</div><div class="col-sm-6">{input}</div></div>',
            'captchaAction' => ['/auth/default/captcha']
        ])
        ?>

        <div class="row">
            <div class="col-md-push-6 col-xs-6">
                <?= Html::submitButton(Yii::t('yee/auth', 'Reset Password'), ['class' => 'btn btn-primary btn-block btn-flat']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>

        <br/>
        <div class="row">
            <div class="col-xs-6">
                <?= Html::a(Yii::t('yee/auth', "Login"), ['default/login']) ?>
            </div>
            <div class="col-xs-6 text-right">
                <?php if ($this->context->module->enableRegistration): ?>
                    <?= Html::a(Yii::t('yee/auth', "Registration"), ['default/signup']) ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>