<?php
/** @var string $content */

use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 text-truncate"><?= Html::encode($this->title) ?></h1>


        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget([
                'links' => $this->params['breadcrumbs'],
                'options' => ['class' => 'breadcrumb flex-nowrap'],
                'itemTemplate' => '<li class="text-truncate breadcrumb-item">{link}</li>',
                'activeItemTemplate' => '<li class="text-truncate breadcrumb-item active" aria-current="page">{link}</li>',
            ]) ?>
        <?php endif ?>



        <div class="row">
            <div class="col-12">

                <?= $content ?>

            </div>
        </div>
    </div>
</main>