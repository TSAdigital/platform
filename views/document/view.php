<?php

use app\models\DocumentAccess;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var app\models\Document $model */
/** @var object $fileDataProvider */
/** @var object $accessDataProvider */
/** @var object $eventDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$script = <<< JS
    $(document).ready(function() {
        $('.modalAccess').click(function() {
            $('#access').modal('show');
        });
    });
JS;
$this->registerJs($script);
?>
<div class="d-grid d-md-block">

    <?php if (Yii::$app->user->can('updateDocument', ['document' => $model])) : ?>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mb-3']) ?>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('accessDocumentAdd', ['document' => $model])) : ?>
        <?= Html::a('Предоставить доступ', '#pills-access', ['class' => 'btn btn-info mb-3 modalAccess']) ?>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('fileUploadDocument', ['document' => $model])) : ?>
        <?= Html::a('Добавить файлы', ['upload', 'id' => $model->id], ['class' => 'btn btn-secondary mb-3']) ?>
    <?php endif; ?>

    <?php if (($model->status === $model::STATUS_DRAFT || $model->status === $model::STATUS_INACTIVE) && Yii::$app->user->can('publishDocument', ['document' => $model])) : ?>
        <?= Html::a('Опубликовать', ['publish', 'id' => $model->id], [
            'class' => 'btn btn-success mb-3',
            'data' => [
                'confirm' => 'Вы уверены, что хотите опубликовать документ?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>

    <?php if ($model->status !== $model::STATUS_INACTIVE && Yii::$app->user->can('cancelDocument', ['document' => $model])) : ?>
        <?= Html::a('Отменить', ['cancel', 'id' => $model->id], [
            'class' => 'btn btn-danger mb-3',
            'data' => [
                'confirm' => 'Вы уверены, что хотите отменить документ?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>

</div>

<div class="card">
    <div class="card-body">
        <div class="overflow-auto custom-scroll">
        <ul class="nav nav-pills mb-2 flex-nowrap" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Основное</button>
            </li>

            <?php if (Yii::$app->user->can('accessDocumentList', ['document' => $model])) : ?>

            <li class="nav-item" role="presentation">
                <button class="nav-link tab-link" id="pills-access-tab" data-bs-toggle="pill" data-bs-target="#pills-access" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Доступ</button>
            </li>

            <?php endif; ?>

            <li class="nav-item" role="presentation">
                <button class="nav-link tab-link" id="pills-file-tab" data-bs-toggle="pill" data-bs-target="#pills-file" type="button" role="tab" aria-controls="pills-file" aria-selected="false">Файлы</button>
            </li>

            <?php if (Yii::$app->user->can('eventDocumentView', ['document' => $model])) : ?>

            <li class="nav-item" role="presentation">
                <button class="nav-link tab-link" id="pills-event-tab" data-bs-toggle="pill" data-bs-target="#pills-event" type="button" role="tab" aria-controls="pills-event" aria-selected="false">События</button>
            </li>

            <?php endif; ?>

        </ul>
        </div>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                <div class="row py-2">
                    <div class="col-12 col-md-auto col-name">
                        <?= $model->getAttributeLabel('name') ?>
                    </div>
                    <div class="col-12 col-md">
                        <?= Html::encode($model->name) ?>
                    </div>
                </div>

                <?php if ($model->description) : ?>

                <div class="row border-top py-2">
                    <div class="col-12 col-md-auto col-name text-bold">
                        <?= $model->getAttributeLabel('description') ?>
                    </div>
                    <div class="col-12 col-md">
                        <?= Html::encode($model->description) ?>
                    </div>
                </div>

                <?php endif; ?>

                <div class="row border-top py-2">
                    <div class="col-12 col-md-auto col-name text-bold">
                        <?= $model->getAttributeLabel('status') ?>
                    </div>
                    <div class="col-12 col-md">
                        <?= $model->getStatusName() ?>
                    </div>
                </div>
                <div class="row border-top py-2">
                    <div class="col-12 col-md-auto col-name text-bold">
                        <?= $model->getAttributeLabel('created_by') ?>
                    </div>
                    <div class="col-12 col-md">
                        <?= Html::encode($model->createdBy->getEmployeeFullName()) ?>
                    </div>
                </div>
                <div class="row border-top py-2">
                    <div class="col-12 col-md-auto col-name text-bold">
                        <?= $model->getAttributeLabel('created_at') ?>
                    </div>
                    <div class="col-12 col-md">
                        <?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at)) ?>
                    </div>
                </div>
                <div class="row border-top pt-2">
                    <div class="col-12 col-md-auto col-name text-bold">
                        Запись обновлена
                    </div>
                    <div class="col-12 col-md">
                        <?= Html::encode(Yii::$app->formatter->asDatetime($model->updated_at)) ?>
                    </div>
                </div>
            </div>

            <?php if (Yii::$app->user->can('accessDocumentList', ['document' => $model])) : ?>

            <div class="tab-pane fade" id="pills-access" role="tabpanel" aria-labelledby="pills-access-tab" tabindex="0">
                <div class="row text-bold">
                    <div class="col-auto text-center py-2 fixed-column fw-bold">#</div>
                    <div class="col py-2 fw-bold">Пользователь</b></div>
                    <div class="col-md-6 d-none d-md-block py-2 fw-bold">Должность</div>
                    <div class="col-auto text-center py-2 fixed-column">

                        <?= Yii::$app->user->can('accessDocumentAdd', ['document' => $model])
                            ?
                            Html::a(Html::tag('svg', '', [
                                'class' => 'align-middle text-success',
                                'data-feather' => 'plus-circle']),
                                '#pills-access',
                                ['class' => 'modalAccess', 'title' => 'Предоставить доступ'
                                ])
                            :
                            Html::tag('svg', '', [
                                'class' => 'align-middle text-success text-muted',
                                'data-feather' => 'plus-circle'
                            ])
                        ?>

                    </div>

                    <?= ListView::widget([
                        'dataProvider' => $accessDataProvider,
                        'emptyText' => '<div class="py-2 border-top">Доступ к документу пока никому не предоставлен.</div>',
                        'itemView' => function ($model, $key, $index, $widget) {
                            $currentPage = $widget->dataProvider->pagination->page;
                            $pageSize = $widget->dataProvider->pagination->pageSize;
                            $rowNumber = $index + 1 + ($currentPage * $pageSize);

                            return $this->render('_access_list', [
                                'model' => $model,
                                'index' => $rowNumber
                            ]);
                        },
                        'layout' => "{items}",
                    ]);
                    ?>

                </div>
                <div class="pagination-container">

                <?php if ($accessDataProvider->pagination->getPageCount() > 1) : ?>

                    <?= LinkPager::widget([
                        'pagination' => $accessDataProvider->pagination,
                        'options' => ['class' => 'mt-3'],
                    ]) ?>

                <?php endif; ?>

                </div>
            </div>

            <?php endif; ?>

            <div class="tab-pane fade" id="pills-file" role="tabpanel" aria-labelledby="pills-file-tab" tabindex="0">
                <div class="row text-bold">
                    <div class="col-auto text-center py-2 fixed-column fw-bold">#</div>
                    <div class="col py-2 fw-bold">Файл</b></div>
                    <div class="col-md-6 d-none d-md-block py-2 fw-bold">Информация</div>
                    <div class="col-auto text-center py-2 fixed-column">

                        <?= Yii::$app->user->can('fileUploadDocument', ['document' => $model])
                            ?
                            Html::a(Html::tag('svg', '', [
                                'class' => 'align-middle text-success',
                                'data-feather' => 'plus-circle']),
                                ['document/upload', 'id' => $model->id],
                                ['title' => 'Добавить файлы']
                            )
                            : Html::tag('svg', '', [
                                    'class' => 'align-middle text-success text-muted',
                                'data-feather' => 'plus-circle'
                            ])
                        ?>

                    </div>

                    <?= ListView::widget([
                        'dataProvider' => $fileDataProvider,
                        'emptyText' => '<div class="py-2 border-top">В данном разделе пока нет файлов.</div>',
                        'itemView' => function ($model, $key, $index, $widget) {
                            $currentPage = $widget->dataProvider->pagination->page;
                            $pageSize = $widget->dataProvider->pagination->pageSize;
                            $rowNumber = $index + 1 + ($currentPage * $pageSize);

                            return $this->render('_file_list', [
                                'model' => $model,
                                'index' => $rowNumber
                            ]);
                        },
                        'layout' => "{items}",
                    ]) ?>

                </div>
                <div class="pagination-container">

                    <?php if ($fileDataProvider->pagination->getPageCount() > 1) : ?>

                        <?= LinkPager::widget([
                            'pagination' => $fileDataProvider->pagination,
                            'options' => ['class' => 'mt-3'],
                        ]) ?>

                    <?php endif; ?>

                </div>
            </div>

            <?php if (Yii::$app->user->can('eventDocumentView', ['document' => $model])) : ?>

            <div class="tab-pane fade" id="pills-event" role="tabpanel" aria-labelledby="pills-event-tab" tabindex="0">

                <div class="row text-bold">
                    <div class="col-auto text-center py-2 fixed-column fw-bold">#</div>
                    <div class="col py-2 fw-bold">Событие</b></div>
                    <div class="col-md-4 d-none d-md-block py-2 text-center fw-bold">Дата и время</div>

                    <?= ListView::widget([
                        'dataProvider' => $eventDataProvider,
                        'emptyText' => '<div class="py-2 border-top">В данном разделе пока нет событий.</div>',
                        'itemView' => function ($model, $key, $index, $widget) {
                            $currentPage = $widget->dataProvider->pagination->page;
                            $pageSize = $widget->dataProvider->pagination->pageSize;
                            $rowNumber = $index + 1 + ($currentPage * $pageSize);

                            return $this->render('_event_list', [
                                'model' => $model,
                                'index' => $rowNumber
                            ]);
                        },
                        'layout' => "{items}",
                    ]) ?>

                </div>
                <div class="pagination-container">

                    <?php if ($eventDataProvider->pagination->getPageCount() > 1) : ?>

                        <?= LinkPager::widget([
                            'pagination' => $eventDataProvider->pagination,
                            'options' => ['class' => 'mt-3'],
                        ]) ?>

                    <?php endif; ?>

                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php if (Yii::$app->user->can('accessDocumentAdd', ['document' => $model])) : ?>

<div class="modal fade" id="access" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="accessLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="accessLabel">Предоставить доступ</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <?= $this->render('_form_access', [
                    'model' => $model,
                    'documentAccess' => new DocumentAccess(),
                ]) ?>

            </div>
        </div>
    </div>
</div>

<?php endif; ?>