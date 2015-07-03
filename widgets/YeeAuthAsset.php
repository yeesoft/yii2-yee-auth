<?php

namespace yeesoft\auth\widgets;

use yii\web\AssetBundle;

/**
 * YeeAuthChoiceAsset is an asset bundle for [[yeesoft\auth\widgets\AuthChoice]] widget.
 */
class YeeAuthAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yeesoft/yii2-yee-auth/assets';
    public $css = [
        'authstyle.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
