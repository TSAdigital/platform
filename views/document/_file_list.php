<?php

/** @var app\models\DocumentFile $model */
/** @var integer $index */
/** @var yii\data\DataProviderInterface $dataProvider */

use yii\bootstrap5\Html;
?>

<div class="row border-top">
    <div class="col-auto text-center py-2 justify-content-center align-self-center text-nowrap fixed-column"><?= $index ?></div>
    <div class="col py-2 justify-content-center align-self-center">
        <?= Yii::$app->user->can('fileDownloadDocument') ? Html::a(Html::encode($model->name), ['document/download', 'id' => $model->id], ['target' => '_blank']) : Html::encode($model->name) ?>
        <span class="d-block d-md-none small">
            <?= implode(' » ',
                [
                    Html::encode($model->createdBy->getEmployeeFullName()),
                    Yii::$app->formatter->asDate($model->created_at),
                    explode('/', $model->type)[1],
                    Html::encode(number_format($model->size / (1024 * 1024), 2) . ' МБ')
                ]
            ); ?>
        </span>
    </div>
    <div class="col-md-6 d-none d-md-block py-2 justify-content-center align-self-center">
        <?= implode(' » ',
            [
                Html::encode($model->createdBy->getEmployeeFullName()),
                Yii::$app->formatter->asDate($model->created_at),
                explode('/', $model->type)[1],
                Html::encode(number_format($model->size / (1024 * 1024), 2) . ' МБ')
            ]
        ); ?>
    </div>
    <div class="col-auto text-center py-2 justify-content-center align-self-center fixed-column">
        <?= Yii::$app->user->can('fileDeleteDocument', ['file' => $model]) ?  Html::a(Html::tag('svg', '', ['class' => 'align-middle text-danger', 'data-feather' => 'trash-2']), ['document/file-delete', 'id' => $model->id], ['data' => [
            'confirm' => 'Вы уверены, что хотите удалить этот файл?',
            'method' => 'post',
        ], 'title' => 'Удалить файл']) : Html::tag('svg', '', ['class' => 'align-middle text-success text-muted', 'data-feather' => 'trash-2'])
        ?>
    </div>
</div>