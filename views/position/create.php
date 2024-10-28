<?php

/** @var yii\web\View $this */
/** @var app\models\Position $model */

$this->title = 'Новая должность';
$this->params['breadcrumbs'][] = ['label' => 'Должности', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>
</div>
