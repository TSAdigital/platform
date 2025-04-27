<?php

/** @var yii\web\View $this */
/* @var $this yii\web\View */
/* @var $data array */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var int $totalDocuments */
/* @var array $uniqueDocumentTypes */
/* @var int $uniqueEmployeesWithDocuments */
/* @var string $latestDocumentDate */
/* @var string $selectedEmployeeName */
/* @var string $selectedPositionName */
/* @var array $positionList */
/* @var bool $enabledDocTypes */
/* @var string $dateFrom */
/* @var string $dateTo */

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\LinkPager;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = 'Зарегистрированные документы в РЭМД с ' . date('d.m.Y', strtotime($dateFrom))  . ' по ' . date('d.m.Y', strtotime($dateTo));
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-flex flex-row gap-1 mb-3">

    <?php if (Yii::$app->user->can('makeRemdSetting')) : ?>

    <div class="btn-group">
        <?= Html::a('Настройки', '#', ['class' => 'btn btn-secondary dropdown-toggle', 'role' => 'button', 'id' => 'dropdownMenuLink', 'data-bs-toggle' => 'dropdown', 'aria-expanded' => false]) ?>

        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <?= Html::a('Основные', ['remd/base-setting'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('Виды документов', ['remd/type-setting'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('Планирование', ['remd/plan'], ['class' => 'dropdown-item']) ?>
            <?= Html::a('Очистить кэш', ['remd/flush-cache'], ['class' => 'dropdown-item', 'data' => ['confirm' => 'Вы уверены, что хотите очистить кэш?', 'method' => 'post',]]) ?>
        </ul>
    </div>

    <?php endif; ?>

    <?= Html::a('Аналитика', ['analytics'], ['class' => 'btn btn-primary']) ?>

    <?= Html::button('Фильтр', ['class' => 'btn btn-dark', 'data-bs-toggle' => 'offcanvas', 'data-bs-target' => '#staticBackdrop', 'aria-controls' => 'staticBackdrop']) ?>

</div>

<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Всего документов</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'clipboard']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= number_format($totalDocuments, 0, '', ' ') ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Всего видов документов</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'type']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= count($uniqueDocumentTypes) ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Всего сотрудников</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'users']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= number_format($uniqueEmployeesWithDocuments, 0, '', ' ') ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Дата обновления</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'calendar']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= $latestDocumentDate ? date('d.m.Y', strtotime($latestDocumentDate)) : ' - ' ?></h1>
            </div>
        </div>
    </div>
</div>

<?php if ($uniqueDocumentTypes) : ?>

    <div class="card">
        <div class="card-body">
            <h3>Распределение документов по видам:</h3>
            <div>

                <?php foreach ($uniqueDocumentTypes as $type): ?>

                    <p class="mb-2 mb-md-0"><?= htmlspecialchars($type['type']) ?>: <span class="text-nowrap fw-bold"><?= number_format($type['count'], 0, '', ' ') ?></span></p>

                <?php endforeach; ?>

            </div>
        </div>
    </div>

<?php endif; ?>



<div class="card">
    <div class="card-body">

        <?php if (empty($data)): ?>

        <p class="mb-0">Нет сотрудников с зарегистрированными документами</p>

        <?php else: ?>

        <div class="row text-bold">
            <div class="col-auto text-center py-2 fixed-column justify-content-center align-self-center fw-bold">#</div>
            <div class="col py-2 fw-bold justify-content-center align-self-center">Сотрудник</div>
            <div class="col-md-3 d-none d-md-block py-2 fw-bold justify-content-center align-self-center">Должность</div>
            <div class="col-md-3 d-none d-md-block py-2 fw-bold text-center justify-content-center align-self-center">Всего документов</div>
            <div class="col-auto text-center py-2 fixed-column"></div>
        </div>

        <?php
        $currentPage = $dataProvider->pagination->page;
        $pageSize = $dataProvider->pagination->pageSize;
        $startNumber = ($currentPage * $pageSize) + 1;
        ?>

        <?php foreach ($data as $index => $item): ?>

        <?php $rowNumber = $startNumber + $index; ?>

        <div class="employee-row" data-id="<?= $item['id'] ?>">
            <div class="row border-top">
                <div class="col-auto text-center py-2 justify-content-center align-self-center fixed-column"><?= $rowNumber ?></div>
                <div class="col py-2 justify-content-center align-self-center">
                    <?= htmlspecialchars($item['full_name']) ?>
                    <div class="d-block d-md-none"><?= htmlspecialchars($item['position']) ?></div>
                    <div class="d-block d-md-none">Всего документов: <?= number_format($item['total_documents'], 0, '', ' ') ?></div>
                </div>
                <div class="col-md-3 d-none d-md-block py-2 justify-content-center align-self-center"><?= htmlspecialchars($item['position']) ?></div>
                <div class="col-md-3 d-none d-md-block py-2 text-center justify-content-center align-self-center"><?= number_format($item['total_documents'], 0, '', ' ') ?></div>
                <div class="col-auto text-center py-2 fixed-column justify-content-center align-self-center">
                    <a href="javascript:void(0)" class="toggle-details text-primary text-decoration-none">
                        <?= Html::tag('svg', '', [
                            'data-feather' => 'chevron-down',
                            'class' => 'toggle-icon',
                            'data-state' => 'closed',
                            'width' => '20',
                            'height' => '20',
                            'stroke-width' => '2'
                        ]) ?>
                    </a>
                </div>
            </div>
            <div class="details-row px-2 pb-2" style="display: none;">

            <?php foreach ($item['document_types'] as $type): ?>
            <?php $isLast = ($type === array_key_last($item['document_types'])); ?>

                <p class="<?= $isLast ? 'mb-0' : 'mb-2 mb-md-1' ?>"><?= htmlspecialchars($type['type']) ?>: <span class="text-nowrap fw-bold"><?= number_format($type['count'], 0, '', ' ') ?></span></p>

            <?php endforeach; ?>

            </div>
        </div>

        <?php endforeach; ?>


        <?php if ($dataProvider->pagination->pageSize < $dataProvider->pagination->totalCount) : ?>

            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'mt-3'],
                'maxButtonCount' => 6,
            ]) ?>

        <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="0" id="staticBackdrop" aria-labelledby="staticBackdropLabel">
    <div class="offcanvas-header">
        <h4 class="offcanvas-title" id="staticBackdropLabel">Фильтр</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <?php
        $layout = <<< HTML
            {input1}
            {separator}
            {input2}
            <div class="input-group-append d-flex align-items-stretch">
                <span class="input-group-text kv-date-remove d-flex align-items-center justify-content-center" style="min-height: calc(1.5em + 0.75rem + 2px);">
                    <svg data-feather="x"></svg>
                </span>
            </div>
         HTML;

        $form = ActiveForm::begin([
            'action' => ['remd/index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1,
                'autocomplete' => 'off',
            ],
        ]);
        ?>

        <div class="form-group">
            <label class="form-label">Дата регистрации</label>
            <?=
            DatePicker::widget([
                'name' => 'date_from',
                'value' => Yii::$app->request->get('date_from'),
                'type' => DatePicker::TYPE_RANGE,
                'name2' => 'date_to',
                'value2' => Yii::$app->request->get('date_to'),
                'separator' => '<svg data-feather="repeat"></svg>',
                'layout' => $layout,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'dd.mm.yyyy',
                    'todayBtn' => true
                ],
                'options' => [
                    'pickerPosition' => 'left',
                ]
            ]);
            ?>
        </div>

        <div class="form-group mt-3">
            <label class="form-label">Вид документа</label>
            <?=
            Select2::widget([
                'name' => 'document_type',
                'value' => Yii::$app->request->get('document_type'),
                'data' => ArrayHelper::map($uniqueDocumentTypes, 'type', 'type'),
                'options' => [
                    'placeholder' => 'Выберите вид документа...',
                    'multiple' => false
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#staticBackdrop',
                ],
            ]);
            ?>
        </div>

        <div class="form-group mt-3">
            <label class="form-label">Сотрудник</label>
            <?= Select2::widget([
                'name' => 'employee_id',
                'value' => Yii::$app->request->get('employee_id'),
                'initValueText' => $selectedEmployeeName,
                'options' => [
                    'placeholder' => 'Введите ФИО сотрудника...',
                    'multiple' => false,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#staticBackdrop',
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => Url::to(['remd/employee-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) {
                            return {
                                q: params.term,
                            };
                        }'),
                        'delay' => 300,
                    ],
                ],
            ]) ?>
        </div>

        <div class="form-group mt-3">
            <label class="form-label">Должность</label>

            <?= Select2::widget([
                'name' => 'position_id',
                'value' => Yii::$app->request->get('position_id'),
                'initValueText' => $selectedPositionName,
                'options' => [
                    'placeholder' => 'Введите должность сотрудника...',
                    'multiple' => false,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#staticBackdrop',
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => Url::to(['remd/position-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) {
                            return {
                                q: params.term,
                            };
                        }'),
                        'delay' => 300,
                    ],
                ],
            ]) ?>
        </div>

        <?php if ($enabledDocTypes): ?>

            <div class="form-group highlight-addon field-all-documents mt-3">
                <div class="custom-control custom-switch">
                    <?= Html::checkbox('all_documents', (Yii::$app->request->get('all_documents') == '1'), [
                        'id' => 'all-documents',
                        'class' => 'custom-control-input',
                        'value' => 1
                    ]) ?>
                    <label class="has-star custom-control-label" for="all-documents">Отображать все виды документов</label>
                </div>
            </div>

        <?php endif; ?>

        <div class="row mt-3">
            <div class="col-8"><?= Html::submitButton('<i class="fas fa-search text-primary"></i>Поиск', ['class' => 'btn btn-primary w-100']) ?></div>
            <div class="col-4"><?= Html::a('<i class="fas fa-redo text-dark"></i>Сброс', ['remd/index'], ['class' => 'btn btn-dark w-100']) ?></div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
.toggle-details:hover .toggle-icon {
    stroke: #0d6efd !important;
    transform: scale(1.1);
    transition: all 0.2s ease;
}
.toggle-icon {
    stroke: #0d6efd;
    cursor: pointer;
    vertical-align: middle;
}

CSS
);

$this->registerJs(<<<JS
$(document).ready(function() {
    feather.replace();
    
    $('.toggle-details').on('click', function(e) {
        e.preventDefault();
        var container = $(this).closest('.employee-row');
        var detailsRow = container.find('.details-row');
        var icon = container.find('.toggle-icon');
        
        if (icon.attr('data-state') === 'closed') {
            detailsRow.slideDown();
            icon.attr('data-state', 'open');
            icon.attr('data-feather', 'chevron-up');
            container.addClass('active');
        } else {
            detailsRow.slideUp();
            icon.attr('data-state', 'closed');
            icon.attr('data-feather', 'chevron-down');
            container.removeClass('active');
        }
        
        feather.replace();
    });
});
JS
);
?>