<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Certificate $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="certificate-form">

    <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

    <?= $form->field($model, 'employee_id')->widget(Select2::class, [
        'data' => $model->getCurrentEmployeeList(),
        'options' => ['placeholder' => 'Выберите сотрудника...'],
        'pluginOptions' => [
            'minimumInputLength' => 3,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Ожидаем результата...'; }"),
            ],
            'ajax' => [
                'url' => Url::to(['employee-list']),
                'dataType' => 'json',
                'delay' => 250,
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
        ],
    ]); ?>

    <?= $form->field($model, 'serial_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'valid_from')->widget(MaskedInput::class,[
        'mask' =>  '99.99.9999',
        'clientOptions' => [
            'onincomplete' => new JsExpression('function() { this.value = null; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'valid_to')->widget(MaskedInput::class,[
        'mask' =>  '99.99.9999',
        'clientOptions' => [
            'onincomplete' => new JsExpression('function() { this.value = null; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'issuer_id')->widget(Select2::class,
        [
            'data' => $model::getActiveIssuerList(),
            'pluginOptions' => [
                'allowClear' => true,
                'placeholder' => 'Выберите издателя...',
            ],
        ]
    ); ?>

    <?= !$model->isNewRecord ? $form->field($model, 'status')->dropDownList($model->getStatusesArray(), ['prompt' => 'Выберите статус...']) : null ?>

    <div class="form-group mt-3">

        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
