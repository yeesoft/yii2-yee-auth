<?php

namespace yeesoft\auth\assets;

use yii\web\AssetBundle;

/**
 * AuthAsset is an asset bundle for [[yeesoft\auth\widgets\AuthChoice]] widget.
 */
class AuthAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yeesoft/yii2-yee-auth/assets/source';
    public $css = [
        'authstyle.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
