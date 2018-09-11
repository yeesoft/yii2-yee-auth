<?php

use yeesoft\helpers\Html;
use yeesoft\theme\assets\CheckboxAsset;
use yeesoft\theme\assets\AdminThemeAsset;

/* @var $this \yii\web\View */
/* @var $content string */

CheckboxAsset::register($this);
AdminThemeAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>

        <?php $this->head() ?>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition login-page">
        <?php $this->beginBody() ?>

        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <div class="callout callout-<?= $type ?>" style="font-size: 16px">
                <a class="pull-right" href="#" onclick="$(this).parent().fadeOut();" style="font-weight: bold; text-decoration: none;">×</a>
                <p style="font-weight: bold;"><?= $message ?></p>
            </div>
        <?php endforeach; ?>
        
        <?= $content ?>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>