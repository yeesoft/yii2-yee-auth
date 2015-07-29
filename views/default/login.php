<?php

/**
 * @var $this yii\web\View
 * @var $model yeesoft\auth\models\forms\LoginForm
 */
use yeesoft\auth\widgets\AuthChoice;
use yeesoft\usermanagement\UserManagementModule;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

    <div id="login-wrapper">
        <div class="row">
            <div class="col-md-6 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?=
                            UserManagementModule::t('front', 'Authorization')
                            ?></h3>
                    </div>
                    <div class="panel-body">

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

                        <?=
                        $form->field($model, 'username')
                            ->textInput(['placeholder' => $model->getAttributeLabel('username'),
                                'autocomplete' => 'off'])
                        ?>

                        <?=
                        $form->field($model, 'password')
                            ->passwordInput(['placeholder' => $model->getAttributeLabel('password'),
                                'autocomplete' => 'off'])
                        ?>

                        <?= $form->field($model, 'rememberMe')->checkbox(['value' => true]) ?>

                        <?=
                        Html::submitButton(
                            UserManagementModule::t('front', 'Login'),
                            ['class' => 'btn btn-lg btn-primary btn-block']
                        )
                        ?>


                        <div class="row registration-block">
                            <div class="col-sm-12">
                                <?=
                                AuthChoice::widget([
                                    'baseAuthUrl' => ['default/oauth'],
                                    'popupMode' => false,
                                ])
                                ?>
                            </div>
                        </div>

                        <div class="row registration-block">
                            <div class="col-sm-6">
                                <?=
                                Html::a(
                                    UserManagementModule::t('front', "Registration"),
                                    ['default/registration']
                                )
                                ?>
                            </div>
                            <div class="col-sm-6 text-right">
                                <?=
                                Html::a(
                                    UserManagementModule::t('front',
                                        "Forgot password ?"),
                                    ['default/password-recovery']
                                )
                                ?>
                            </div>
                        </div>


                        <?php ActiveForm::end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$css = <<<CSS

#login-wrapper {
	position: relative;
	top: 30%;
}
#login-wrapper .registration-block {
	margin-top: 15px;
}
CSS;

$this->registerCss($css);
?>