<?php

use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employee $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="employee-form">

    <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'middle_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'birth_date')->widget(MaskedInput::class,[
        'mask' =>  '99.99.9999',
        'clientOptions' => [
            'onincomplete' => new JsExpression('function() { this.value = null; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'user_id')->widget(Select2::class, [
    'data' => $model->getCurrentUserList(),
        'options' => ['placeholder' => 'Выберите пользователя...'],
        'pluginOptions' => [
            'minimumInputLength' => 3,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Ожидаем результата...'; }"),
            ],
            'ajax' => [
                'url' => Url::to(['user-list']),
                'dataType' => 'json',
                'delay' => 250,
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
        ],
    ]); ?>

    <?= $form->field($model, 'position_id')->widget(Select2::class,
        [
            'data' => $model::getActivePositionList(),
            'pluginOptions' => [
                'allowClear' => true,
                'placeholder' => 'Выберите должность...',
            ],
        ]
    ); ?>

    <?= !$model->isNewRecord ? $form->field($model, 'status')->dropDownList($model->getStatusesArray(), ['prompt' => 'Выберите статус...']) : null ?>

    <div class="form-group">

        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
