<?php

/** @var app\models\DocumentEvent $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<div class="row border-top">
    <div class="col-auto text-center py-2 justify-content-center align-self-center text-nowrap fixed-column"><?= $index ?></div>
    <div class="col py-2 justify-content-center align-self-center">
        <?= $model->user->getEmployeeFullName() . ' ' . Html::encode($model->event) ?>
        <span class="d-block d-md-none small"><?= Yii::$app->formatter->asDatetime($model->created_at); ?></span>
    </div>
    <div class="col-md-4 d-none d-md-block py-2 justify-content-center align-self-center text-center"><?= Yii::$app->formatter->asDatetime($model->created_at); ?></div>
</div>