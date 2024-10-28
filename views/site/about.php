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
        и предоставляется на условиях "как есть" без каких-либо гарантий. Пользователи несут полную ответственность за использование сервиса и все связанные с этим риски. Разработчик не несет ответственности за любые убытки или ущерб, возникшие в результате использования или невозможности использования сервиса.
    </div>
</div>
