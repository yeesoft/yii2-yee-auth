<?php

namespace yeesoft\auth\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * AvatarAsset is an asset bundle for avatar image.
 */
class AvatarAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yeesoft/yii2-yee-auth/assets/source/avatar';

    public static function getDefaultAvatar($size = 'small')
    {
        $avatars = [
            'small' => 'images/avatar-48x48.png',
            'medium' => 'images/avatar-96x96.png',
            'large' => 'images/avatar-144x144.png',
        ];

        if (isset(Yii::$app->assetManager->bundles[self::className()]) && isset($avatars[$size])) {
            return Yii::$app->assetManager->bundles[self::className()]->baseUrl . '/' . $avatars[$size];
        }

        return false;
    }
}