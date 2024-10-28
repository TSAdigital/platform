<?php

/** @var app\models\DocumentFile $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<tr>
    <td class="text-center text-nowrap"><?= $index ?></td>
    <td class="col-11 col-md-6"><?= Yii::$app->user->can('fileDownloadDocument') ? Html::a(Html::encode($model->name), ['document/download', 'id' => $model->id], ['target' => '_blank']) : Html::encode($model->name) ?></td>
    <td class="d-none d-md-table-cell col-md-6">
        <?= implode(' » ',
            [
                Html::encode($model->createdBy->getEmployeeFullName()),
                Yii::$app->formatter->asDate($model->created_at),
                Html::encode(number_format($model->size / (1024 * 1024), 2) . ' МБ')
            ]
        ); ?>
    </td>
    <td class="text-center">
        <?= Yii::$app->user->can('fileDeleteDocument', ['file' => $model]) ?  Html::a(Html::tag('svg', '', ['class' => 'align-middle text-danger', 'data-feather' => 'trash-2']), ['document/file-delete', 'id' => $model->id], ['data' => [
            'confirm' => 'Вы уверены, что хотите удалить этот файл?',
            'method' => 'post',
        ], 'title' => 'Удалить файл']) : Html::tag('svg', '', ['class' => 'align-middle text-success text-muted', 'data-feather' => 'trash-2'])?>
    </td>
</tr>