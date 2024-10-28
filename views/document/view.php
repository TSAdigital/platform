<?php

use app\models\DocumentAccess;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\DetailView;
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
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
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
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">

                <?= DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
                    'attributes' => [
                        [
                            'attribute' => 'name',
                            'captionOptions' => ['width' => '170px'],
                        ],
                        [
                            'attribute' => 'description',
                            'format' => 'ntext',
                            'visible' => !empty($model->description),
                        ],
                        [
                            'attribute' => 'status',
                            'value' => $model->getStatusName()
                        ],
                        [
                            'attribute' => 'created_by',
                            'value' => $model->createdBy->getEmployeeFullName(),
                        ],
                        'created_at:datetime',
                        'updated_at:datetime',
                    ],
                ]) ?>

            </div>

            <?php if (Yii::$app->user->can('accessDocumentList', ['document' => $model])) : ?>

            <div class="tab-pane fade" id="pills-access" role="tabpanel" aria-labelledby="pills-access-tab" tabindex="0">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="col-12">Пользователь</th>
                        <th class="text-center"><?= Yii::$app->user->can('accessDocumentAdd', ['document' => $model]) ? Html::a(Html::tag('svg', '', ['class' => 'align-middle text-success', 'data-feather' => 'plus-circle']), '#pills-access', ['class' => 'modalAccess', 'title' => 'Предоставить доступ']) : Html::tag('svg', '', ['class' => 'align-middle text-success text-muted', 'data-feather' => 'plus-circle'])  ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?= ListView::widget([
                        'dataProvider' => $accessDataProvider,
                        'emptyText' => '<td colspan="3">Доступ к документу пока никому не предоставлен.</td>',
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

                    </tbody>
                </table>

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
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="col-11 col-md-6">Файл</th>
                        <th class="d-none d-md-table-cell col-md-6">Информация</th>
                        <th class="text-center"><?= Yii::$app->user->can('fileUploadDocument', ['document' => $model]) ? Html::a(Html::tag('svg', '', ['class' => 'align-middle text-success', 'data-feather' => 'plus-circle']), ['document/upload', 'id' => $model->id], ['title' => 'Добавить файлы']) : Html::tag('svg', '', ['class' => 'align-middle text-success text-muted', 'data-feather' => 'plus-circle'])  ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?= ListView::widget([
                        'dataProvider' => $fileDataProvider,
                        'emptyText' => '<td colspan="4">В данном разделе пока нет файлов.</td>',
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

                    </tbody>
                </table>
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
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="col-11 col-md-8">Событие</th>
                        <th class="d-none d-md-table-cell col-md-4 text-center">Дата и время</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?= ListView::widget([
                        'dataProvider' => $eventDataProvider,
                        'emptyText' => '<td colspan="4">В данном разделе пока нет событий.</td>',
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

                    </tbody>
                </table>
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
                <h5 class="modal-title" id="accessLabel">Предоставить доступ</h5>
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