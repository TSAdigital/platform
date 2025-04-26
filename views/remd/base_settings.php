<?php

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\RemdBaseSetting */
/* @var array $years */

$this->title = 'Базовые настройки документов РЭМД';
$this->params['breadcrumbs'][] = ['label' => 'Зарегистрированные документы в РЭМД', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'date_from')->widget(DatePicker::class, [
            'options' => ['placeholder' => 'Укажите дату...'],
            'value' => 'dd.mm.yyyy',
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose' => true,
                'todayBtn' => true,
                'todayHighlight' => true,
            ],
            'pickerIcon' => '<svg data-feather="calendar"></svg>',
            'removeIcon' => '<svg data-feather="x"></svg>',
        ]) ?>

        <?= $form->field($model, 'date_to')->widget(DatePicker::class, [
            'options' => ['placeholder' => 'Укажите дату...'],
            'value' => 'dd.mm.yyyy',
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose' => true,
                'todayBtn' => true,
                'todayHighlight' => true,
            ],
            'pickerIcon' => '<svg data-feather="calendar"></svg>',
            'removeIcon' => '<svg data-feather="x"></svg>',
        ]) ?>

        <?= $form->field($model, 'date_of_update')->widget(DatePicker::class, [
            'options' => ['placeholder' => 'Укажите дату...'],
            'value' => 'dd.mm.yyyy',
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose' => true,
                'todayBtn' => true,
                'todayHighlight' => true,
            ],
            'pickerIcon' => '<svg data-feather="calendar"></svg>',
            'removeIcon' => '<svg data-feather="x"></svg>',
        ]) ?>

        <?= $form->field($model, 'page_size')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'analytics_period')->widget(Select2::class,
            [
                'data' => array_combine($years, $years),
                'pluginOptions' => [
                    'allowClear' => true,
                    'placeholder' => 'Выберите год...',
                ],
            ]
        ); ?>

        <?= $form->field($model, 'chart_type')->widget(Select2::class,
            [
                'data' => [
                        'linear' => 'Линейный',
                        'logarithmic' => 'Логарифмический',
                    ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'placeholder' => 'Выберите тип графика...',
                ],
            ]
        ); ?>

        <?= $form->field($model, 'hide_empty_months')->checkbox() ?>

        <?= $form->field($model, 'lk_document_filter_enabled')->checkbox() ?>

        <?= $form->field($model, 'use_caching')->checkbox() ?>

        <div class="form-group mt-3">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
.form-check-input{
    margin-top: 0 !important;
}
CSS
);
?>