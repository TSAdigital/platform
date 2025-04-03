<?php

/** @var app\models\Certificate $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<div class="row border-top">
    <div class="col-auto text-center py-2 justify-content-center align-self-center text-nowrap fixed-column"><?= $index ?></div>
    <div class="col py-2 justify-content-center align-self-center">
        <?= Html::a(Html::encode($model->employee->getFullName()) ?? 'Не найден', ['certificate/view', 'id' => $model->id], ['target' => '_blank']) ?>
        <span class="d-block d-xl-none small"><?= $model->issuer->name ?></span>
        <span class="d-block d-md-none small"><?= $model->getStatusName() ?> » <?= $model->valid_to ?></span>
    </div>
    <div class="col-xl-2 d-none d-xl-block py-2 justify-content-center align-self-center text-truncate"><?= $model->issuer->name ?></div>
    <div class="col-md-2 d-none d-md-block py-2 text-center justify-content-center align-self-center"><?= $model->valid_from ?></div>
    <div class="col-md-2 d-none d-md-block py-2 text-center justify-content-center align-self-center"><?= $model->valid_to ?></div>
    <div class="col-md-2 d-none d-md-block py-2 text-center justify-content-center align-self-center"><?= $model->getStatusName() ?></div>
</div>