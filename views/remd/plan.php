<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var array $groupedModels */
/* @var $pagination \yii\data\Pagination */

$this->title = 'Планирование документов РЕМД';
$this->params['breadcrumbs'][] = ['label' => 'Зарегистрированные документы в РЭМД', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-grid d-md-block">
    <?= Html::a('Добавить', ['create-plan'], ['class' => 'btn btn-success mb-3']) ?>
</div>

<div class="card">
    <div class="card-body">

        <?php if ($groupedModels) : ?>

            <?php $firstYear = true; ?>

            <?php foreach ($groupedModels as $year => $plans) : ?>

                <h4 class="<?= !$firstYear ? 'mt-3' : '' ?>"><?= Html::encode($year) ?> год</h4>

                <?php $firstYear = false; ?>

                <?php foreach ($plans as $plan) : ?>

                    <div class="row py-2">
                        <div class="col-11">

                            <?= Html::a(
                                $plan->type ? Html::encode($plan->type) : 'Все документы',
                                ['update-plan', 'id' => $plan->id],
                                ['class' => 'text-decoration-none']
                            ) ?>

                        </div>
                        <div class="col-1 text-end">
                            <?= Html::a(
                                '<svg data-feather="info" class="text-danger"></svg>',
                                ['delete-plan', 'id' => $plan->id],
                                [
                                    'title' => 'Удалить',
                                    'data' => [
                                        'confirm' => 'Вы уверены, что хотите удалить этот план?',
                                        'method' => 'post',
                                    ],
                                ]
                            ) ?>
                        </div>
                    </div>


                <?php endforeach; ?>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="mb-0">Нет данных для отображения.</p>

        <?php endif; ?>


        <?php if ($pagination->getPageCount() > 1) : ?>

            <div class="mt-3">

                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination mb-0'],
                    'maxButtonCount' => 6,
                ]) ?>

            </div>

        <?php endif; ?>

    </div>
</div>
