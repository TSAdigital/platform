<?php

/** @var app\models\DocumentAccess $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<tr>
    <td class="text-center text-nowrap"><?= $index ?></td>
    <td class="col-12"><?= Html::encode($model->user->getEmployeeFullNameAndPosition()) ?></td>
    <td class="text-center">
        <?= Yii::$app->user->can('accessDocumentCancel', ['access' => $model]) ?  Html::a(Html::tag('svg', '', ['class' => 'align-middle text-danger', 'data-feather' => 'x-circle']), ['document/cancel-access', 'id' => $model->id], ['data' => [
            'confirm' => 'Вы уверены, что хотите отменить доступ для этого пользователя?',
            'method' => 'post',
        ], 'title' => 'Отменить доступ']) : Html::tag('svg', '', ['class' => 'align-middle text-success text-muted', 'data-feather' => 'x-circle'])?>
    </td>
</tr>
