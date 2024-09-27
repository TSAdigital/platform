<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'О проекте';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-about">
    Данный сервис был разработан <span class="badge bg-danger">НЕ</span> программистом

    <?= Html::a('@starhanov', 'https://t.me/starhanov', ['target' => '_blank']) ?>

    для облегчения жизни простых тружеников.
</div>
