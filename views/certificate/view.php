<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Certificate $model */

$this->title = $model->employee->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Сертификаты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-grid d-md-block">

    <?php if (Yii::$app->user->can('updateCertificate')) : ?>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mb-3']) ?>
    <?php endif; ?>

</div>

<div class="card">
    <div class="card-body">
        <div class="row pb-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('employee_id') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->employee->getFullName()) ?>
            </div>
        </div>

        <?php if ($model->serial_number) : ?>

            <div class="row border-top py-2">
                <div class="col-12 col-md-auto col-name text-bold">
                    <?= $model->getAttributeLabel('serial_number') ?>
                </div>
                <div class="col-12 col-md">
                    <?= Html::encode($model->serial_number) ?>
                </div>
            </div>

        <?php endif; ?>

        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('valid_from') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->valid_from) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('valid_to') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->valid_to) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('issuer_id') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->issuer->name) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('status') ?>
            </div>
            <div class="col-12 col-md">
                <?= $model->getStatusName() ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('created_at') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at)) ?>
            </div>
        </div>
        <div class="row border-top pt-2">
            <div class="col-12 col-md-auto col-name text-bold">
                Запись обновлена
            </div>
            <div class="col-12 col-md">
                <?= Html::encode(Yii::$app->formatter->asDatetime($model->updated_at)) ?>
            </div>
        </div>
    </div>
</div>
