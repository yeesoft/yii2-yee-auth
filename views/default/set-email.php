<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\SetEmailForm $model
 */
$this->title = Yii::t('yee/auth', 'Set E-mail');

$col12 = $this->context->module->gridColumns;
$col9 = (int) ($col12 * 3 / 4);
$col6 = (int) ($col12 / 2);
$col3 = (int) ($col12 / 4);
?>

<div id="update-wrapper">
    <div class="row">
        <div class="col-md-<?= $col6 ?> col-md-offset-<?= $col3 ?>">
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

                    <?= $form->field($model, 'email')->textInput(['maxlength' => 255, 'autofocus' => false]) ?>

                    <div class="form-group">
                        <div class="col-sm-offset-<?= $col3 ?> col-sm-<?= $col9 ?>">
                            <?= Html::submitButton(Yii::t('yee/auth', 'Confirm'), ['class' => 'btn btn-lg btn-primary btn-block']) ?>
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









