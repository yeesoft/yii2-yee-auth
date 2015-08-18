<?php

use yeesoft\Yee;

/**
 * @var yii\web\View $this
 * @var yeesoft\models\User $user
 */

$this->title = Yee::t('front', 'E-mail confirmed');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="change-own-password-success">

    <div class="alert alert-success text-center">
        <?= Yee::t('front', 'E-mail confirmed') ?> - <b><?= $user->email ?></b>
    </div>

</div>
