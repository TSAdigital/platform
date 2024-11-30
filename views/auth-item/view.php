<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AuthItem $model */

$this->title = $model->description;
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-grid d-md-block">

    <?php if (Yii::$app->user->can('updateRole') && $model->name != 'administrator') : ?>
        <?= Html::a('Редактировать', ['update', 'name' => $model->name], ['class' => 'btn btn-primary mb-3'])?>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('permissionUpdateRole') && $model->name != 'administrator') : ?>
        <?= Html::a('Настройки доступа', ['permission-update', 'name' => $model->name], ['class' => 'btn btn-secondary mb-3']) ?>
    <?php endif; ?>

    <?php if ($model->status === $model::STATUS_ACTIVE && Yii::$app->user->can('blockRole') && $model->name != 'administrator') : ?>
        <?= Html::a('Заблокировать', ['block', 'name' => $model->name], [
            'class' => 'btn btn-danger mb-3',
            'data' => [
                'confirm' => 'Вы уверены, что хотите заблокировать роль?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>

    <?php if ($model->status === $model::STATUS_INACTIVE && Yii::$app->user->can('unlockRole') && $model->name != 'administrator') : ?>
        <?= Html::a('Разблокировать', ['unlock', 'name' => $model->name], [
            'class' => 'btn btn-success mb-3',
            'data' => [
                'confirm' => 'Вы уверены, что хотите разблокировать роль?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>

</div>

<div class="card">
    <div class="card-body">
        <div class="row pb-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('description') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->description) ?>
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
