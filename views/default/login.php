<?php
/* @var $this yii\web\View */
/* @var $model yeesoft\auth\models\forms\LoginForm */

use yeesoft\auth\widgets\AuthChoice;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('yee/auth', 'Authorization');
?>

<div class="login-box">
    <?php if ($this->context->module->logo): ?>
            <?= $this->render($this->context->module->logo) ?>
    <?php endif; ?>
        
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <?php
        $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'options' => ['autocomplete' => 'off'],
                    'validateOnBlur' => false,
                    'fieldConfig' => [
                        'template' => "{input}\n{error}",
                    ],
                ])
        ?>

        <?= $form->field($model, 'username')->textInput(['placeholder' => $model->getAttributeLabel('username'), 'autocomplete' => 'off']) ?>

        <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->getAttributeLabel('password'), 'autocomplete' => 'off']) ?>

        <div class="row">
            <div class="col-xs-8">
                <?= $form->field($model, 'rememberMe')->checkbox(['value' => true]) ?>
            </div>
            <div class="col-xs-4">
                <?= Html::submitButton(Yii::t('yee/auth', 'Sign In'), ['class' => 'btn btn-primary btn-block btn-flat']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>

        <?php if ($this->context->module->enableOAuth): ?>
            <div class="social-auth-links text-center">
                <p>- OR -</p>
                <?= AuthChoice::widget(['baseAuthUrl' => ['/auth/oauth/index', 'language' => false]]) ?>
            </div>
        <?php endif; ?>

        <div class="row registration-block">
            <div class="col-xs-6">
                <?php if ($this->context->module->enableRegistration): ?>
                    <?= Html::a(Yii::t('yee/auth', "Registration"), ['default/signup']) ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-6 text-right">
                <?php if ($this->context->module->enablePasswordReset): ?>
                    <?= Html::a(Yii::t('yee/auth', "Forgot password?"), ['default/password-reset']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>