<?php

use yeesoft\Yee;

/**
 * @var yii\web\View $this
 */

$this->title = Yee::t('back', 'Change own password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="change-own-password-success">

    <div class="alert alert-success text-center">
        <?= Yee::t('back', 'Password has been changed') ?>
    </div>

</div>
