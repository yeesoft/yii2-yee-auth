<?php

namespace yeesoft\auth\assets;

use yii\web\AssetBundle;

/**
 * AvatarUploaderAsset is an asset bundle for avatar upload widget.
 */
class AvatarUploadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yeesoft/yii2-yee-auth/assets/source/avatar-upload';
    public $css = ['css/avatar-upload.css'];
    public $js = ['js/avatar-upload.js'];
    public $depends = ['yii\web\JqueryAsset'];

}
