<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">
    <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

    <input type="text" style="display:none" autocomplete="username" />
    <input type="password" style="display:none" autocomplete="password" />

    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>

    <?= $model->isNewRecord ? $form->field($model, 'password')->passwordInput(['maxlength' => true, 'autocomplete' => 'off']) : null ?>

    <?= $model->isNewRecord ? $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true, 'autocomplete' => 'off']) : null ?>

    <?= $form->field($model, 'role')->dropDownList($model::getRolesList($model->getCurrentRole()), [
        'prompt' => 'Выберите роль',
        'options' => [$model->getCurrentRole() => ['Selected' => true]]
    ]) ?>

    <?= $form->field($model, 'telegram_chat_id')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>

    <div class="form-group mt-3">

        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
