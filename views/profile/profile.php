<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yeesoft\auth\widgets\AuthChoice;
use yeesoft\auth\assets\AvatarAsset;
use yeesoft\auth\assets\AvatarUploadAsset;

/* @var $this yii\web\View  */
/* @var $model yeesoft\auth\models\forms\SetEmailForm  */

$this->title = Yii::t('yee/auth', 'User Profile');
$this->params['breadcrumbs'][] = $this->title;

AvatarUploadAsset::register($this);
AvatarAsset::register($this);

$avatar = Yii::$app->user->identity->getAvatar('large');
$isDefaultAvatar = ($avatar) ? false : true;
$avatar = ($avatar) ? $avatar : AvatarAsset::getDefaultAvatar('large');
?>

<div class="profile-index">

    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-9">
            <span class="h4"><?= $this->title ?></span>
        </div>
        <div class="text-right col-md-3">
            <?= Html::a(Yii::t('yee/auth', 'Update Password'), ['/auth/profile/update-password'], ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="avatar-upload">
                <?php
                ActiveForm::begin([
                    'method' => 'post',
                    'action' => Url::to(['/auth/profile/upload-avatar']),
                    'options' => ['enctype' => 'multipart/form-data', 'autocomplete' => 'off'],
                ])
                ?>

                <?= Html::fileInput('image') ?>

                <div class="avatar-preview" data-default-avatar="<?= $avatar ?>">
                    <img src="<?= $avatar ?>"/>
                    <div class="upload-button <?= ($isDefaultAvatar) ? '' : 'hidden' ?>">
                        <i class="fa fa-cloud-upload fa-2x"></i><br/>
                        <span>Click To Upload</span>
                    </div>
                    <div class="remove-button <?= ($isDefaultAvatar) ? 'hidden' : '' ?>" 
                         data-action="<?= Url::to(['/auth/profile/remove-avatar']) ?>" 
                         title="<?= Yii::t('yee/auth', 'Remove profile picture') ?>" 
                         data-toggle="tooltip"
                         data-placement="right">
                        <i class="fa fa-remove fa-lg"></i><br/>
                    </div>
                </div>

                <div class="upload-status"></div>

                <?php ActiveForm::end() ?>
            </div>

            <?php if ($this->context->module->enableOAuth): ?>
                <div class="oauth-services">
                    <div class="oauth-authorized-services">
                        <div class="label label-primary space-down"
                             title="<?= Yii::t('yee/auth', 'Click to unlink') ?>" data-toggle="tooltip"
                             data-placement="right">
                            <?= Yii::t('yee/auth', 'Authorized Services') ?>:
                        </div>

                        <?=
                        AuthChoice::widget([
                            'baseAuthUrl' => ['/auth/profile/unlink-client', 'language' => false],
                            'displayClients' => AuthChoice::DISPLAY_AUTHORIZED,
                            'popupMode' => false,
                            'shortView' => true,
                        ])
                        ?>
                    </div>

                    <div>
                        <div class="label label-primary space-down"
                             title="<?= Yii::t('yee/auth', 'Click to connect') ?>" data-toggle="tooltip"
                             data-placement="right">
                            <?= Yii::t('yee/auth', 'Not Authorized Services') ?>:
                        </div>

                        <?=
                        AuthChoice::widget([
                            'baseAuthUrl' => ['/auth/oauth/index', 'language' => false],
                            'displayClients' => AuthChoice::DISPLAY_NON_AUTHORIZED,
                            'popupMode' => false,
                            'shortView' => true,
                        ])
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
        $form = ActiveForm::begin([
                    'id' => 'user',
                    'validateOnBlur' => false,
                ])
        ?>

        <div class="col-md-9">

            <div class="panel panel-default">
                <div class="panel-body">

                    <?= $form->field($user, 'username')->textInput(['maxlength' => 255, 'autofocus' => false]) ?>

                    <?= $form->field($user, 'email')->textInput(['maxlength' => 255, 'autofocus' => false]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($user, 'first_name')->textInput(['maxlength' => 124]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($user, 'last_name')->textInput(['maxlength' => 124]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($user, 'gender')->dropDownList(yeesoft\models\User::getGenderList()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?php //echo $form->field($user, 'birth_day')->textInput(['maxlength' => 2])  ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($user, 'skype')->textInput(['maxlength' => 64]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($user, 'phone')->textInput(['maxlength' => 24]) ?>
                        </div>
                    </div>

                    <?php //echo $form->field($user, 'info')->textarea(['maxlength' => 255])  ?>

                </div>
            </div>

            <?= Html::submitButton(Yii::t('yee/auth', 'Save Profile'), ['class' => 'btn btn-primary']) ?>


        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>



<?php
$confRemovingAuthMessage = Yii::t('yee/auth', 'Are you sure you want to unlink this service?');
$confRemovingAvatarMessage = Yii::t('yee/auth', 'Are you sure you want to delete your profile picture?');
$js = <<<JS
confRemovingAuthMessage = "{$confRemovingAuthMessage}";
confRemovingAvatarMessage = "{$confRemovingAvatarMessage}";
$('.oauth-authorized-services .auth-client a').on('click', function () {
        return confirm(confRemovingAuthMessage);
});
JS;

$css = <<<CSS
.oauth-services {
    margin-top: 15px;
}

.oauth-services > div {
    margin-top: 10px;
}

.space-down {
    margin-bottom: 5px;
    display: block !important;
}
CSS;

$this->registerJs($js, yii\web\View::POS_READY);
$this->registerCss($css);
?>
