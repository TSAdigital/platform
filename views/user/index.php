<?php

use app\models\User;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $pageSize */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
$script = <<< JS
    $('#page-size').on('change', function() {
        var pageSize = $(this).val();
        $.pjax.reload({
            container: '#pjax-user',
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

                <?php if (Yii::$app->user->can('createUser')) : ?>

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

        <?php Pjax::begin(['id' => 'pjax-user']); ?>

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
                    'attribute' => 'username',
                    'headerOptions' => ['class' => 'col-12 col-md-4 align-middle'],
                    'filterInputOptions' => ['class' => 'form-control', 'autocomplete' => 'off'],
                    'contentOptions' => ['class' => 'col-12 col-md-4 align-middle'],
                    'format' => 'raw',
                    'value' => function($model) {
                        return Html::a(Html::encode($model->username), ['view', 'id' => $model->id], ['data-pjax' => 0]);
                    },
                ],
                [
                    'attribute' => 'role',
                    'headerOptions' => ['class' => 'd-none d-md-table-cell col-md-4 align-middle text-center align-middle'],
                    'contentOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'filterOptions' => ['class' => 'd-none d-md-table-cell col-md-4'],
                    'filter' => array_intersect_key(User::getRolesArray(), array_flip(User::getAvailableRoles())),
                    'filterInputOptions' => [
                        'class' => 'form-select',
                    ],
                    'label' => 'Роль',
                    'value' => function ($model) {
                        if (isset($model->authAssignments[0]->itemName->description)) {
                            switch ($model->authAssignments[0]->item_name) {
                                case 'administrator':
                                    return '<span class="badge bg-danger">' . $model->authAssignments[0]->itemName->description . '</span>';
                                default:
                                    return '<span class="badge bg-primary">' . $model->authAssignments[0]->itemName->description . '</span>';
                            }
                        } return '<span class="badge bg-secondary">Нет</span>';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'status',
                    'headerOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'contentOptions' => ['class' => 'd-none d-md-table-cell col-md-4 text-center align-middle'],
                    'filterOptions' => ['class' => 'd-none d-md-table-cell col-md-4'],
                    'filter' => array_intersect_key(User::getStatusesArray(), array_flip(User::getAvailableStatuses())),
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
            'pager' => [
                'maxButtonCount' => 6,
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
