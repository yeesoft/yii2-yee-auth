<?php

use yeesoft\Yee;

/**
 * @var yii\web\View $this
 * @var yeesoft\models\User $user
 */

$this->title = Yee::t('front', 'Registration - confirm your e-mail');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="registration-wait-for-confirmation">

    <div class="alert alert-info text-center">
        <?= Yee::t('front', 'Check your e-mail {email} for instructions to activate account', [
            'email' => '<b>' . $user->email . '</b>'
        ]) ?>
    </div>

</div>
