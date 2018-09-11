<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yeesoft\auth\models\forms\UpdatePasswordForm;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\UpdatePasswordForm $model
 */
$this->title = Yii::t('yee/auth', 'Update Password');
?>

<div class="login-box">
    <?php if ($this->context->module->logo): ?>
        <?= $this->render($this->context->module->logo) ?>
    <?php endif; ?>
    
    <div class="login-box-body">
        <p class="login-box-msg"><?= $this->title ?></p>

        <?php
        $form = ActiveForm::begin([
                    'id' => 'update-form',
                    'options' => ['autocomplete' => 'off'],
                    'validateOnBlur' => false,
                ])
        ?>

        <?php if ($model->scenario != UpdatePasswordForm::SCENARIO_EMAIL_RESET): ?>
            <?= $form->field($model, 'current_password')->passwordInput(['maxlength' => 255]) ?>
        <?php endif; ?>

        <?= $form->field($model, 'password')->passwordInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'repeat_password')->passwordInput(['maxlength' => 255]) ?>

        <div class="row">
            <div class="col-md-push-6 col-xs-6">
                <?= Html::submitButton(Yii::t('yee', 'Update'), ['class' => 'btn btn-primary btn-block']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>