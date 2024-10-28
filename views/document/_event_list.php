<?php

/** @var app\models\DocumentEvent $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<tr>
    <td class="text-center text-nowrap"><?= $index ?></td>
    <td class="col-11 col-md-8"><?= $model->user->getEmployeeFullName() . ' ' . Html::encode($model->event) ?></td>
    <td class="d-none d-md-table-cell col-md-4 text-center"><?= Yii::$app->formatter->asDatetime($model->created_at); ?></td>
</tr>