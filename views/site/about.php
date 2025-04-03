<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'О проекте';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">
        Данный сервис был разработан <span class="badge bg-danger">НЕ</span> программистом
        <?= Html::a('@starhanov', 'https://t.me/starhanov', ['target' => '_blank']) ?>
        и предоставляется на условиях <b>«как есть»</b> без каких-либо гарантий. <b>Пользователи несут полную ответственность</b> за использование сервиса и все связанные с этим риски. <b>Разработчик не несет ответственности</b> за любые убытки или ущерб, возникшие в результате использования или невозможности использования сервиса.
    </div>
</div>
