<?php

use app\models\Document;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Документы';
$this->params['breadcrumbs'][] = $this->title;
$script = <<< JS
    $('#page-size').on('change', function() {
        var pageSize = $(this).val();
        $.pjax.reload({
            container: '#pjax-document',
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

                <?php if (Yii::$app->user->can('createDocument')) : ?>

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

        <?php Pjax::begin(['id' => 'pjax-document']); ?>

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
                    'attribute' => 'name',
                    'headerOptions' => ['class' => 'col-12 col-md-8 align-middle'],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off'],
                    'contentOptions' => ['class' => 'col-12 col-md-8 align-middle'],
                    'format' => 'raw',
                    'value' => function($model) {
                        return implode(PHP_EOL, [
                            !$model->isReadByUser() ? Html::tag('svg', '', ['class' => 'text-success', 'data-feather' => 'target', 'style' => 'width: 12px; height: 12px; margin-top:-2px']) : null,
                            Html::a(Html::encode($model->name), ['view', 'id' => $model->id], ['data-pjax' => 0]),
                            Html::tag('br'),
                            Html::beginTag('small'),
                            $model->createdBy->getEmployeeFullName(),
                            Yii::$app->formatter->asDate($model->created_at),
                            Html::endTag('small'),
                        ]);
                    },
                ],

                [
                    'attribute' => 'status',
                    'headerOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'contentOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'filterOptions' => ['class' => 'd-none d-md-table-cell col-md-4'],
                    'filter' => array_intersect_key(Document::getStatusesArray(), array_flip(Document::getAvailableStatuses())),
                    'filterInputOptions' => [
                        'class' => 'form-select',
                    ],
                    'value' => function ($model) {
                        switch ($model->status) {
                            case Document::STATUS_ACTIVE:
                                return '<span class="badge bg-success">'. $model->getStatusName() .'</span>';
                            case Document::STATUS_INACTIVE:
                                return '<span class="badge bg-danger">'. $model->getStatusName() .'</span>';
                            case Document::STATUS_DRAFT:
                                return '<span class="badge bg-dark">'. $model->getStatusName() .'</span>';
                            default:
                                return '<span class="badge bg-light">Неизвестный статус</span>';
                        }
                    },
                    'format' => 'raw',
                ],
            ],
            'pager' => [
                'maxButtonCount' => 6,
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
