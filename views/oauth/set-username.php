<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\SetUsernameForm $model
 */
$this->title = Yii::t('yee/auth', 'Set Username');
?>

<div class="login-box">
    <?php if ($this->context->module->logo): ?>
        <?= $this->render($this->context->module->logo) ?>
    <?php endif; ?>

    <div class="login-box-body">
        <p class="login-box-msg"><?= $this->title ?></p>

        <?php $form = ActiveForm::begin(['validateOnBlur' => false]) ?>

        <?= $form->field($model, 'username')->textInput(['minlength' => 4, 'maxlength' => 255, 'autofocus' => false]) ?>

        <div class="row">
            <div class="col-sm-offset-6 col-sm-6">
                <?= Html::submitButton(Yii::t('yee', 'Confirm'), ['class' => 'btn btn-primary btn-block']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>










