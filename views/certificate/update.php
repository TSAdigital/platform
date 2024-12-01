<?php

/** @var yii\web\View $this */
/** @var app\models\Certificate $model */

$this->title = 'Редактирование сертификата: ' . $model->employee->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Сертификаты', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' =>  $model->employee->getFullName(), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>
</div>

