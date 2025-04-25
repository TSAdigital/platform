<?php

use kartik\form\ActiveForm;
use kartik\select2\Select2;

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RemdPlan $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $years */
/** @var array $docTypes */
?>

<?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>
    <h4 class="mb-3">Общие сведения</h4>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'year')->widget(Select2::class,
                [
                    'data' => array_combine($years, $years),
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => 'Выберите год...',
                    ],
                ]
            ); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'type')->widget(Select2::class,
                [
                    'data' => array_combine($docTypes, $docTypes),
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => 'Выберите вид документа...',
                    ],
                ]
            ); ?>
        </div>
        <div class="col-md-4"><?= $form->field($model, 'year_plan')->textInput(['type' => 'number'])  ?></div>
    </div>

    <h4 class="mb-3">1 Квартал</h4>

    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'jan')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'feb')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'mar')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'q1')->textInput(['type' => 'number']) ?></div>
    </div>

    <h4 class="mb-3">2 Квартал</h4>

    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'apr')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'may')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'jun')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'q2')->textInput(['type' => 'number']) ?></div>
    </div>

    <h4 class="mb-3">3 Квартал</h4>

    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'jul')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'aug')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'sep')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'q3')->textInput(['type' => 'number']) ?></div>
    </div>

    <h4 class="mb-3">4 Квартал</h4>
    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'oct')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'nov')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'dec')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'q4')->textInput(['type' => 'number']) ?></div>
    </div>

    <div class="form-group mt-2">

        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>

    </div>

<?php ActiveForm::end(); ?>