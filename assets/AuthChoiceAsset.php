<?php

namespace yeesoft\auth\assets;

use yii\web\AssetBundle;

/**
 * AuthChoiceAsset is an asset bundle for [[yeesoft\auth\widgets\AuthChoice]] widget.
 */
class AuthChoiceAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yeesoft/yii2-yee-auth/assets/source/auth-choice';
    public $css = [
        'css/auth-choice.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
