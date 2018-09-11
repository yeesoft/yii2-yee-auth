<?php

use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Html;
use yeesoft\auth\widgets\AuthChoice;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\RegistrationForm $model
 */
$this->title = Yii::t('yee/auth', 'Signup');
?>

<div class="register-box">
    <?php if ($this->context->module->logo): ?>
        <?= $this->render($this->context->module->logo) ?>
    <?php endif; ?>

    <div class="register-box-body">
        <p class="login-box-msg">Register a new membership</p>

        <?php $form = ActiveForm::begin(['id' => 'signup', 'validateOnBlur' => false, 'options' => ['autocomplete' => 'off']]) ?>

        <?= $form->field($model, 'username')->textInput(['maxlength' => 50]) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'password')->passwordInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'repeat_password')->passwordInput(['maxlength' => 255]) ?>

        <?=
        $form->field($model, 'captcha')->widget(Captcha::className(), [
            'template' => '<div class="row"><div class="col-sm-6">{image}</div><div class="col-sm-6">{input}</div></div>',
            'captchaAction' => ['/auth/default/captcha']
        ])
        ?>

        <div class="row">
            <div class="col-xs-8">
                <?php if (Yii::$app->controller->module->termsAndConditions): ?>
                    <div class="checkbox icheck">
                        <label>
                            <?= $form->field($model, 'terms')->checkbox(['value' => true, 'label' => Yii::t('yee/auth', 'I agree to the {link}', ['link' => Html::a(Yii::t('yee/auth', 'Terms & Conditions'), Yii::$app->controller->module->termsAndConditions)])]) ?>
                        </label>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-xs-4">
                <?= Html::submitButton(Yii::t('yee/auth', 'Register'), ['class' => 'btn btn-primary btn-block btn-flat']) ?>
            </div>
        </div>

        <?php if ($this->context->module->enableOAuth): ?>
            <div class="social-auth-links text-center">
                <p>- OR -</p>
                <?= AuthChoice::widget(['baseAuthUrl' => ['/auth/oauth/index', 'language' => false]]) ?>
            </div>
        <?php endif; ?>

        <div class="row registration-block">
            <div class="col-xs-6">
                <?= Html::a(Yii::t('yee/auth', "Login"), ['default/login']) ?>
            </div>
            <div class="col-xs-6 text-right">
                <?php if ($this->context->module->enablePasswordReset): ?>
                    <?= Html::a(Yii::t('yee/auth', "Forgot password?"), ['default/password-reset']) ?>
                <?php endif; ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>