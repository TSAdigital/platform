<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception$exception */

use yii\helpers\Html;

$this->title = $name;
?>

<div class="site-error">
    <div class="text-center">
        <h1 class="display-6 fw-bold"><?= Html::encode($this->title) ?></h1>
        <p class="lead"><?= nl2br(Html::encode($message)) ?></p>

        <?= Html::a(Yii::t('app', 'На главную'), ['/site/index'], ['class' => 'btn btn-primary']) ?>

    </div>
</div>
