<?php
/** @var string $content */

use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>

        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>

        <?= $content ?>

    </div>
</main>