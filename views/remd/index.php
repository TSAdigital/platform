<?php
/** @var yii\web\View $this */
/* @var string $dateFrom */
/* @var string $dateTo */
/* @var string $documentType */
/* @var int|null $employeeId */
/* @var int|null $positionId */
/* @var int|null $allDocuments */
/* @var bool $enabledDocTypes */
/* @var string $selectedEmployeeName */
/* @var string $selectedPositionName */
/* @var array $uniqueDocumentTypes */
/* @var int $limit */

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$titleDateFrom = isset($_GET['date_from']) ? date('d.m.Y', strtotime($_GET['date_from'])) : date('d.m.Y', strtotime($dateFrom));
$titleDateTo = isset($_GET['date_to']) ? date('d.m.Y', strtotime($_GET['date_to'])) : date('d.m.Y', strtotime($dateTo));

$this->title = 'Зарегистрированные документы в РЭМД с ' . $titleDateFrom . ' по ' . $titleDateTo;
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
                <div class="d-flex align-items-center" style="height: 48px;">
                    <h1 class="mt-1 mb-0" id="all-document-count">
                        <span class="placeholder col-4"></span>
                    </h1>
                    <div class="spinner-border spinner-stats spinner-border-sm ms-2 text-primary" role="status" id="documents-spinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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
                <div class="d-flex align-items-center" style="height: 48px;">
                    <h1 class="mt-1 mb-0" id="all-types-count">
                        <span class="placeholder col-3"></span>
                    </h1>
                    <div class="spinner-border spinner-stats spinner-border-sm ms-2 text-primary" role="status" id="types-spinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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
                <div class="d-flex align-items-center" style="height: 48px;">
                    <h1 class="mt-1 mb-0" id="all-employees-count">
                        <span class="placeholder col-3"></span>
                    </h1>
                    <div class="spinner-border spinner-stats spinner-border-sm ms-2 text-primary" role="status" id="employees-spinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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
                <div class="d-flex align-items-center" style="height: 48px;">
                    <h1 class="mt-1 mb-0" id="update-date">
                        <span class="placeholder col-6"></span>
                    </h1>
                    <div class="spinner-border spinner-stats spinner-border-sm ms-2 text-primary" role="status" id="date-spinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3>Распределение документов по видам:</h3>
        <div id="document-types-stats">
            <p class="mb-2 mb-md-0">Загрузка данных...</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="employees-list-container">
            <div id="employees-header"></div>
            <div id="employees-list" class="employee-row"></div>
            <div id="employees-loading" class="text-center py-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-2 mb-0">Загрузка данных...</p>
            </div>
            <div id="employees-load-more" class="text-center" style="display: none;">
            </div>
        </div>
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
                'data' => array_combine($uniqueDocumentTypes, $uniqueDocumentTypes),
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
                        var enabledTypes = $(this).data("enabledDocTypes");
                        return {
                            q: params.term,
                            enabledDocTypes: enabledTypes ? JSON.stringify(enabledTypes) : null
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
                        var enabledTypes = $(this).data("enabledDocTypes");
                        return {
                            q: params.term,
                            enabledDocTypes: enabledTypes ? JSON.stringify(enabledTypes) : null
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
            <div class="col-4"><?= Html::a('<i class="fas fa-redo text-dark"></i>Сброс', '#', ['id' => 'reset-filter', 'class' => 'btn btn-dark w-100']) ?></div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" id="employeeStatsModal" tabindex="-1" aria-labelledby="employeeStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="employeeStatsModalLabel">Статистика документов сотрудника</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="employee-stats-container">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-2">Загрузка данных...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$url = Url::to(['remd/get-stats']);
$js = <<<JS
    $(document).ready(function() {
        $('.spinner-border').show();
        $('.placeholder').addClass('placeholder-glow');
        $('#document-types-stats').html('<p class="mb-2 mb-md-0">Загрузка данных...</p>');
    });
    
    function resetValues() {
        $('#all-document-count').html('<span class="placeholder col-4"></span>');
        $('#all-types-count').html('<span class="placeholder col-3"></span>');
        $('#all-employees-count').html('<span class="placeholder col-3"></span>');
        $('#update-date').html('<span class="placeholder col-6"></span>');
        $('#document-types-stats').html('<p class="mb-2 mb-md-0">Загрузка данных...</p>');
        
        $('.placeholder').addClass('placeholder-glow');
        $('.spinner-border').show();
    }
    
    function numberFormat(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }
    
    function loadStatistics() {
        var formData = $('#w0').serialize();
        
        $.get('$url', formData)
        .done(function(data) {
            var formattedDate = formatDate(data.updateDate);
            
            $('#all-document-count').html(numberFormat(data.allDocumentCount));
            $('#all-types-count').html(numberFormat(data.allTypesCount));
            $('#all-employees-count').html(numberFormat(data.allEmployeesCount));
            $('#update-date').html(formattedDate);
            
            $('.spinner-stats').hide();
            $('.placeholder').removeClass('placeholder-glow');
            
            updateTitleAndBreadcrumbs();
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Ошибка запроса:", textStatus, errorThrown);
    
            $('#all-document-count').html('<span class="text-danger">Ошибка</span>');
            $('#all-types-count').html('<span class="text-danger">Ошибка</span>');
            $('#all-employees-count').html('<span class="text-danger">Ошибка</span>');
            $('#update-date').html('<span class="text-danger">Ошибка</span>');
            $('.spinner-border').hide();
            
            if (typeof toastr !== 'undefined') {
                toastr.error('Не удалось загрузить данные', 'Ошибка');
            }
        });
    }
    
    function updateTitleAndBreadcrumbs() {
        var dateFrom = $('[name="date_from"]').val() || '$dateFrom';
        var dateTo = $('[name="date_to"]').val() || '$dateTo';
        
        var formattedFrom = formatDisplayDate(dateFrom);
        var formattedTo = formatDisplayDate(dateTo);
        
        var newTitle = 'Зарегистрированные документы в РЭМД с ' + formattedFrom + ' по ' + formattedTo;
        
        document.title = newTitle;
        
        $('.breadcrumb li:last').text(newTitle);
        
        $('h1.h3').text(newTitle);
    }
    
    function formatDisplayDate(dateString) {
        if (!dateString) return '';
        var parts = dateString.split('.');
        if (parts.length === 3) {
            return dateString;
        }
        
        var date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        var day = String(date.getDate()).padStart(2, '0');
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var year = date.getFullYear();
        
        return day + '.' + month + '.' + year;
    }
    
    function formatDate(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        var day = String(date.getDate()).padStart(2, '0');
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var year = date.getFullYear();
        
        return day + '.' + month + '.' + year;
    }
    
    loadStatistics();
    
    $('#w0').on('submit', function(e) {
        e.preventDefault();
        resetValues();
        loadStatistics();
        $('.offcanvas').offcanvas('hide'); 
    });
    
    $('#reset-filter').on('click', function(e) {
        e.preventDefault();
        
        $('#w0')[0].reset();
        
        $('[name="document_type"]').val(null).trigger('change');
        $('[name="employee_id"]').val(null).trigger('change');
        $('[name="position_id"]').val(null).trigger('change');
        
        resetValues();
    
        loadStatistics();
    
        $('.offcanvas').offcanvas('hide');
        
        history.pushState(null, '', $(this).attr('href'));
    });
JS;

$this->registerJs($js);

$typesStatsUrl = Url::to(['remd/get-document-types-stats']);
$typesStatsJs = <<<JS
    $(document).ready(function() {
        function loadDocumentTypesStats() {
            var formData = $('#w0').serialize();
            var container = $('#document-types-stats');
            
            container.html('<p class="mb-2 mb-md-0">Загрузка данных...</p>');
            
            $.get('$typesStatsUrl', formData)
                .done(function(data) {
                    if (data && data.length > 0) {
                        var html = '';
                        $.each(data, function(index, item) {
                            html += '<p class="mb-2 mb-md-0">' + item.type + ': <span class="text-nowrap fw-bold">' + numberFormat(item.count) + '</span></p>';
                        });
                        container.html(html);
                    } else {
                        container.html('<p class="mb-2 mb-md-0">Нет данных</p>');
                    }
                })
                .fail(function() {
                    container.html('<p class="text-danger">Ошибка загрузки данных</p>');
                });
        }
        
        loadDocumentTypesStats();
    
        $('#w0').on('submit', function(e) {
            e.preventDefault();
            loadDocumentTypesStats();
        });
        
        $('#reset-filter').on('click', function(e) {
            e.preventDefault();
            setTimeout(function() {
                loadDocumentTypesStats();
            }, 100);
        });
    });
JS;

$this->registerJs($typesStatsJs);
?>

<?php
$employeeDocumentsUrl = Url::to(['remd/employee-documents']);
$jsEmployees = <<<JS
    $(document).ready(function() {
        let offset = 0;
        const limit = $limit;
        let isLoading = false;
        let hasMore = true;
        
        loadEmployees();
    
        function showLoadingIndicator() {
            if (offset === 0) {
                $('#employees-list').html(
                    '<div class="text-center py-4" id="employees-loading">' +
                        '<div class="spinner-border text-primary" role="status">' +
                            '<span class="visually-hidden">Загрузка...</span>' +
                        '</div>' +
                        '<p class="mt-2 mb-0">Загрузка данных...</p>' +
                    '</div>'
                );
            } else {
                $('#load-more-btn').prop('disabled', true);
                $('#employees-load-more').append(
                    '<div class="spinner-border spinner-border-sm text-primary mt-2" role="status" id="loading-spinner">' +
                        '<span class="visually-hidden">Загрузка...</span>' +
                    '</div>'
                );
            }
        }
        
        function hideLoadingIndicator() {
            $('#employees-loading').remove();
            $('#loading-spinner').remove();
            $('#load-more-btn').prop('disabled', false);
            $('#employees-header').html(`
                <div class="row text-bold">
                <div class="col-auto text-center py-2 fixed-column justify-content-center align-self-center fw-bold">#</div>
                <div class="col py-2 fw-bold justify-content-center align-self-center">Сотрудник</div>
                <div class="col-md-3 d-none d-md-block py-2 fw-bold justify-content-center align-self-center">Должность</div>
                <div class="col-md-3 d-none d-md-block py-2 fw-bold text-center justify-content-center align-self-center">Всего документов</div>
                </div>
            `)
        }
    
        function loadEmployees() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        showLoadingIndicator();
        
        const formData = $('#w0').serialize();
        
        $.get('$employeeDocumentsUrl', formData + '&offset=' + offset + '&limit=' + limit)
            .done(function(response) {
                if (response.employees.length > 0) {
                    renderEmployees(response.employees, offset);
                    offset += response.employees.length;
                    hasMore = response.hasMore;
                    
                    if (hasMore) {
                        $('#employees-load-more').show();
                    } else {
                        $('#employees-load-more').hide();
                    }
                    
                    $('#employees-list').data('has-data', true);
                } else if (offset === 0) {
                    $('#employees-list').html('<p class="text-center py-4 mb-0">Нет данных для отображения</p>');
    
                    $('#employees-list').data('has-data', false);
                    hasMore = false;
                }
            })
            .fail(function() {
                $('#employees-list').html('<p class="text-center py-4 text-danger">Ошибка загрузки данных</p>');
                $('#employees-list').data('has-data', false);
                hasMore = false;
            })
            .always(function() {
                isLoading = false;
                hideLoadingIndicator();
            });
        }
    
        $(document).on('touchmove', function(e) {
            if ($('#employees-list').data('has-data') === false) {
                e.preventDefault(); 
                return false;
            }
        });
    
        $(window).on('scroll', function() {
            if ($('#employees-list').data('has-data') === false) {
                return;
            }
    
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                loadEmployees();
            }
        });
    
        function renderEmployees(employees, startIndex) {
    
            let html = '';
            
            employees.forEach(function(employee, index) {
                const num = startIndex + index + 1;
                const fullName = [employee.last_name, employee.first_name, employee.middle_name].filter(Boolean).join(' ');
                
                html += '<div class="row border-top employee-item" data-employee-id="' + employee.id + '">' +
                        '<div class="col-auto text-center py-2 justify-content-center align-self-center fixed-column">' + num + '</div>' +
                        '<div class="col py-2 justify-content-center align-self-center">' +
                            '<a href="javascript:void(0)" class="text-primary-employee">' + fullName + '</a>' +
                            '<div class="d-block d-md-none">' + (employee.position_name || '-') + '</div>' +
                            '<div class="d-block d-md-none">Всего документов: <b>' + numberFormat(employee.document_count) + '</b></div>' +
                        '</div>' +
                        '<div class="col-md-3 d-none d-md-block py-2 justify-content-center align-self-center">' + (employee.position_name || '-') + '</div>' +
                        '<div class="col-md-3 d-none d-md-block py-2 text-center justify-content-center align-self-center">' + numberFormat(employee.document_count) + '</div>' +
                    '</div>';
            });
            
            $('#employees-header').show();
            
            if (startIndex === 0) {
                $('#employees-list').html(html);
            } else {
                $('#employees-list').append(html);
            }
        }
    
        $(window).on('scroll', function() {
            if ($(window).scrollTop() + $(window).height() > $(document).height() - 300) {
                loadEmployees();
            }
        });
        
        $('#w0').on('submit', function(e) {
            e.preventDefault();
            $('#employees-header').hide();
            offset = 0;
            hasMore = true;
            loadEmployees();
            $('.offcanvas').offcanvas('hide');
        });
        
        $('#reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#employees-header').hide();
            offset = 0;
            hasMore = true;
            setTimeout(function() {
                loadEmployees();
            }, 100);
        });
    });
JS;

$this->registerJs($jsEmployees);
?>

<?php
$employeeDocumentTypesUrl = Url::to(['remd/employee-document-types']);
$jsModal = <<<JS
    $(document).ready(function() {
        $(document).on('click', '.employee-item, .text-primary-employee', function(e) {
            e.stopPropagation();
            const item = $(this).hasClass('employee-item') ? $(this) : $(this).closest('.employee-item');
            const employeeId = item.data('employee-id');
            const employeeName = item.find('.text-primary-employee').text().trim();
    
            const modal = new bootstrap.Modal(document.getElementById('employeeStatsModal'));
            $('#employeeStatsModalLabel').text(employeeName);
            modal.show();
    
            loadEmployeeStats(employeeId);
        });
        
        function loadEmployeeStats(employeeId) {
            const formData = $('#w0').serialize();
            
            $('#employee-stats-container').html(
                '<div class="text-center py-4">' +
                    '<div class="spinner-border text-primary" role="status">' +
                        '<span class="visually-hidden">Загрузка...</span>' +
                    '</div>' +
                    '<p class="mt-2">Загрузка данных...</p>' +
                '</div>'
            );
            
            $.get('$employeeDocumentTypesUrl', formData + '&selected_employee_id=' + employeeId)
                .done(function(data) {
                    if (data.error) {
                        $('#employee-stats-container').html(
                            '<p class="text-center py-4 text-danger">' + data.error + '</p>'
                        );
                        return;
                    }
                    
                    if (data.stats && data.stats.length > 0) {
                        renderEmployeeStats(data);
                    } else {
                        $('#employee-stats-container').html(
                            '<p class="text-center py-4">Нет данных о документах</p>'
                        );
                    }
                })
                .fail(function() {
                    $('#employee-stats-container').html(
                        '<p class="text-center py-4 text-danger">Ошибка загрузки данных</p>'
                    );
                });
        }
    
        function renderEmployeeStats(data) {
            const employeeName = [
                data.employee.last_name, 
                data.employee.first_name, 
                data.employee.middle_name
            ].filter(Boolean).join(' ');
            
            let html = 
                    '<div class="row py-2 mx-md-2">' +
                        '<div class="col-9 col-md-6 fw-bold text-truncate">Вид документа</div>' +
                        '<div class="col-3 col-md-2 text-center fw-bold text-truncate">Количество</div>' +
                        '<div class="d-none d-md-block col-md-4 text-center fw-bold text-truncate">Последний документ</div>' +
                    '</div>';
            
            data.stats.forEach(function(item) {
                const lastDate = item.last_date ? new Date(item.last_date).toLocaleDateString() : '-';
                html += 
                    '<div class="row border-top py-2 mx-md-2">' +
                        '<div class="col-9 col-md-6 justify-content-center align-self-center">' + item.type + '</div>' +
                        '<div class="col-3 col-md-2 text-center justify-content-center align-self-center">' + numberFormat(item.count) + '</div>' +
                        '<div class="d-none d-md-block col-md-4 text-center justify-content-center align-self-center">' + lastDate + '</div>' +
                    '</div>';
            });
            
            $('#employee-stats-container').html(html);
        }
    
        $('#employeeStatsModal').on('hidden.bs.modal', function() {
            $('#employee-stats-container').html(
                '<div class="text-center py-4">' +
                    '<div class="spinner-border text-primary" role="status">' +
                        '<span class="visually-hidden">Загрузка...</span>' +
                    '</div>' +
                    '<p class="mt-2">Загрузка данных...</p>' +
                '</div>'
            );
        });
    });
JS;

$this->registerJs($jsModal);
?>