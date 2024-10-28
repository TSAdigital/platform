<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'autocomplete' => 'off']) ?>
    <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true, 'autocomplete' => 'off']) ?>

    <div class="form-group mt-3">

        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
