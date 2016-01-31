<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\SetUsernameForm $model
 */
$this->title = Yii::t('yee/auth', 'Set Username');
?>

<div id="update-wrapper">
    <div class="row">
        <div class="col-md-6 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= $this->title ?></h3>
                </div>

                <div class="panel-body">

                    <?php $form = ActiveForm::begin([
                        'id' => 'user',
                        'layout' => 'horizontal',
                        'validateOnBlur' => false,
                    ]); ?>

                    <?= $form->field($model, 'username')->textInput(['minlength' => 4, 'maxlength' => 255, 'autofocus' => false]) ?>

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <?= Html::submitButton(Yii::t('yee', 'Confirm'), ['class' => 'btn btn-lg btn-primary btn-block']) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>

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









