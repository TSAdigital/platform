<?php

/** @var yii\web\View $this */
/** @var app\models\Issuer $model */

$this->title = 'Редактирование удостоверяющего центра: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Удостоверяющие центры', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>
</div>
