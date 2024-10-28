<?php

/** @var yii\web\View $this */
/** @var app\models\AuthItem $model */

$this->title = 'Редактирование роли: ' . $model->description;
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->description, 'url' => ['view', 'name' => $model->name]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>
</div>
