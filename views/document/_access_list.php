<?php

/** @var app\models\DocumentAccess $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<div class="row border-top">
    <div class="col-auto text-center py-2 justify-content-center align-self-center text-nowrap fixed-column"><?= $index ?></div>
    <div class="col py-2 justify-content-center align-self-center">
        <?= Html::encode($model->user->getEmployeeFullName()) ?>
        <span class="d-block d-md-none small"><?= isset($model->user->employee->position->name) ? Html::encode($model->user->employee->position->name) : 'Должность не указана' ?></span>
    </div>
    <div class="col-md-6 d-none d-md-block py-2 justify-content-center align-self-center"><?= isset($model->user->employee->position->name) ? Html::encode($model->user->employee->position->name) : 'Должность не указана' ?></div>
    <div class="col-auto text-center py-2 justify-content-center align-self-center fixed-column">
        <?= Yii::$app->user->can('accessDocumentCancel', ['access' => $model]) ?  Html::a(Html::tag('svg', '', ['class' => 'align-middle text-danger', 'data-feather' => 'x-circle']), ['document/cancel-access', 'id' => $model->id], ['data' => [
            'confirm' => 'Вы уверены, что хотите отменить доступ для этого пользователя?',
            'method' => 'post',
        ], 'title' => 'Отменить доступ']) : Html::tag('svg', '', ['class' => 'align-middle text-success text-muted', 'data-feather' => 'x-circle'])
        ?>
    </div>
</div>
