<?php

/* @var $this yii\web\View */
/* @var $model app\models\RemdPlan */
/* @var array $docTypes */
/* @var array $years */

$this->title = 'Новый план документов РЕМД';
$this->params['breadcrumbs'][] = ['label' => 'Зарегистрированные документы в РЭМД', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Планирование документов РЕМД', 'url' => ['plan']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form_plan', [
            'model' => $model,
            'docTypes' => $docTypes,
            'years' => $years,
        ]) ?>

    </div>
</div>