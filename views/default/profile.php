<?php

use yeesoft\auth\assets\AvatarAsset;
use yeesoft\auth\assets\AvatarUploaderAsset;
use yeesoft\auth\widgets\AuthChoice;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var yeesoft\auth\models\forms\SetEmailForm $model
 */
$this->title = Yii::t('yee/auth', 'User Profile');
$this->params['breadcrumbs'][] = $this->title;

AvatarUploaderAsset::register($this);
AvatarAsset::register($this);
?>

<div class="panel panel-default">

    <div class="panel-heading">
        <?= Html::a(Yii::t('yee/auth', 'Update Password'), ['/auth/default/update-password'], ['class' => 'btn btn-primary btn-sm pull-right', 'style' => 'margin-top:-4px']) ?>
        <h3 class="panel-title "><?= $this->title ?></h3>
    </div>

    <div class="panel-body">

        <div class="row">
            <div class="col-md-3">

                <div class="image-uploader">
                    <?php ActiveForm::begin([
                        'method' => 'post',
                        'action' => Url::to(['/auth/default/upload-avatar']),
                        'options' => ['enctype' => 'multipart/form-data', 'autocomplete' => 'off'],
                    ]) ?>

                    <?php $avatar = ($userAvatar = Yii::$app->user->identity->getAvatar('large')) ? $userAvatar : AvatarAsset::getDefaultAvatar('large') ?>
                    <div class="image-preview" data-default-avatar="<?= $avatar ?>">
                        <img src="<?= $avatar ?>"/>
                    </div>
                    <div class="image-actions">
                        <span class="btn btn-primary btn-file"
                              title="<?= Yii::t('yee/auth', 'Change profile picture') ?>" data-toggle="tooltip"
                              data-placement="left">
                            <i class="fa fa-folder-open fa-lg"></i>
                            <?= Html::fileInput('image', null, ['class' => 'image-input']) ?>
                        </span>

                        <?= Html::submitButton('<i class="fa fa-save fa-lg"></i>', [
                            'class' => 'btn btn-primary image-submit',
                            'title' => Yii::t('yee/auth', 'Save profile picture'),
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                        ]) ?>

                        <span class="btn btn-primary image-remove"
                              data-action="<?= Url::to(['/auth/default/remove-avatar']) ?>"
                              title="<?= Yii::t('yee/auth', 'Remove profile picture') ?>" data-toggle="tooltip"
                              data-placement="right">
                            <i class="fa fa-remove fa-lg"></i>
                        </span>
                    </div>
                    <div class="upload-status"></div>

                    <?php ActiveForm::end() ?>
                </div>

                <div class="oauth-services">
                    <div>
                        <div class="label label-primary space-down"
                             title="<?= Yii::t('yee/auth', 'Click to unlink service') ?>" data-toggle="tooltip"
                             data-placement="right">
                            <?= Yii::t('yee/auth', 'Authorized Services') ?>:
                        </div>

                        <?= AuthChoice::widget([
                            'baseAuthUrl' => ['/auth/default/unlink-oauth', 'language' => false],
                            'displayClients' => AuthChoice::DISPLAY_AUTHORIZED,
                            'popupMode' => false,
                            'shortView' => true,
                        ]) ?>
                    </div>

                    <div>
                        <div class="label label-primary space-down"
                             title="<?= Yii::t('yee/auth', 'Click to connect with service') ?>" data-toggle="tooltip"
                             data-placement="right">
                            <?= Yii::t('yee/auth', 'Non Authorized Services') ?>:
                        </div>

                        <?= AuthChoice::widget([
                            'baseAuthUrl' => ['/auth/default/oauth', 'language' => false],
                            'displayClients' => AuthChoice::DISPLAY_NON_AUTHORIZED,
                            'popupMode' => false,
                            'shortView' => true,
                        ]) ?>
                    </div>
                </div>

            </div>

            <div class="col-md-9">

                <?php $form = ActiveForm::begin([
                    'id' => 'user',
                    'layout' => 'horizontal',
                    'validateOnBlur' => false,
                ]) ?>

                <?= $form->field($model, 'username')->textInput(['maxlength' => 255, 'autofocus' => false]) ?>

                <?= $form->field($model, 'email')->textInput(['maxlength' => 255, 'autofocus' => false]) ?>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <?= Html::submitButton(Yii::t('yee/auth', 'Save Profile'), ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>

            </div>

        </div>
    </div>
</div>