<?php

use app\models\Employee;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\EmployeeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Сотрудники';
$this->params['breadcrumbs'][] = $this->title;
$script = <<< JS
    $('#page-size').on('change', function() {
        var pageSize = $(this).val();
        $.pjax.reload({
            container: '#pjax-employee',
            url: window.location.href,
            data: { pageSize: pageSize },
            replace: false
        });
    });
JS;
$this->registerJs($script);
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-row">
            <div class="flex-grow-1">

                <?php if (Yii::$app->user->can('createEmployee')) : ?>

                    <p><?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?></p>

                <?php else: ?>

                    <p><?= Html::a('Добавить', '#', ['class' => 'btn btn-success disabled']) ?></p>

                <?php endif; ?>

            </div>
            <div class="col-auto" style="min-width: 50px;">

                <?= Html::dropDownList('pageSize', $searchModel->pageSize, [10 => 10, 20 => 20, 50 => 50, 100 => 100], [
                    'id' => 'page-size',
                    'class' => 'form-select text-center align-middle',
                ]) ?>

            </div>
        </div>

        <?php Pjax::begin(['id' => 'pjax-employee']); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-responsive no-border'],
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['class' => 'text-center align-middle'],
                    'contentOptions' => ['class' => 'text-center align-middle', 'style' => 'min-width: 50px; !important;'],
                ],
                [
                    'attribute' => 'full_name',
                    'headerOptions' => ['class' => 'col-12 col-md-4 align-middle'],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off'],
                    'contentOptions' => ['class' => 'col-12 col-md-4 align-middle'],
                    'format' => 'raw',
                    'value' => function($model) {
                        return Html::a(Html::encode($model->getFullName()), ['view', 'id' => $model->id], ['data-pjax' => 0]);
                    },
                ],
                [
                    'attribute' => 'position_name',
                    'headerOptions' => ['class' => 'd-none d-md-table-cell col-md-4 align-middle'],
                    'contentOptions' => ['class' => 'd-none d-md-table-cell col-md-4 align-middle'],
                    'filterOptions' => ['class' => 'd-none d-md-table-cell col-md-4'],
                    'format' => 'raw',
                    'value' => function($model) {
                        return Html::a(Html::encode($model->position ? $model->position->name : null), ['view', 'id' => $model->id], ['data-pjax' => 0]);
                    },
                ],
                [
                    'attribute' => 'status',
                    'headerOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'contentOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'filterOptions' => ['class' => 'd-none d-md-table-cell col-md-4'],
                    'filter' => array_intersect_key(Employee::getStatusesArray(), array_flip(Employee::getAvailableStatuses())),
                    'filterInputOptions' => [
                        'class' => 'form-select',
                    ],
                    'value' => function ($model) {
                        switch ($model->status) {
                            case $model::STATUS_ACTIVE:
                                return '<span class="badge bg-success">'. $model->getStatusName() .'</span>';
                            case $model::STATUS_INACTIVE:
                                return '<span class="badge bg-danger">'. $model->getStatusName() .'</span>';
                            default:
                                return '<span class="badge bg-light">Неизвестный статус</span>';
                        }
                    },
                    'format' => 'raw',
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
