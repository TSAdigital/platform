<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Employee $model */

$this->title = $model->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-grid d-md-block">

    <?php if (Yii::$app->user->can('updateEmployee')) : ?>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mb-3']) ?>
    <?php endif; ?>

</div>

<div class="card">
    <div class="card-body">
        <div class="row pb-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('last_name') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->last_name) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('first_name') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->first_name) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('middle_name') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->middle_name) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('birth_date') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->birth_date) ?>
            </div>
        </div>

        <?php if (isset($model->user->username)) : ?>

        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('user_id') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->user->username) ?>
            </div>
        </div>

        <?php endif; ?>

        <?php if (isset($model->position->name)) : ?>

        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('position_id') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->position->name) ?>
            </div>
        </div>

        <?php endif; ?>

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
