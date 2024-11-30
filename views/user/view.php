<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-grid d-md-block">

    <?php if (Yii::$app->user->can('updateUser')) : ?>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary mb-3']) ?>
    <?php endif; ?>

    <?php if (Yii::$app->user->can('changePasswordUser')) : ?>
        <?= Html::a('Изменить пароль', ['change-password', 'id' => $model->id], ['class' => 'btn btn-secondary mb-3']) ?>
    <?php endif; ?>

    <?php if ($model->status === $model::STATUS_ACTIVE && Yii::$app->user->can('blockUser')) : ?>
        <?= Html::a('Заблокировать', ['block', 'id' => $model->id], [
            'class' => 'btn btn-danger mb-3',
            'data' => [
                'confirm' => 'Вы уверены, что хотите заблокировать пользователя?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>

    <?php if ($model->status === $model::STATUS_INACTIVE && Yii::$app->user->can('unlockUser')) : ?>
        <?= Html::a('Разблокировать', ['unlock', 'id' => $model->id], [
            'class' => 'btn btn-success mb-3',
            'data' => [
                'confirm' => 'Вы уверены, что хотите разблокировать пользователя?',
                'method' => 'post',
            ],
        ]) ?>
    <?php endif; ?>

</div>

<div class="card">
    <div class="card-body">
        <div class="row pb-2">
            <div class="col-12 col-md-auto col-name">
                <?= $model->getAttributeLabel('username') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->username) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('role') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->getRoleName()) ?>
            </div>
        </div>
        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('email') ?>
            </div>
            <div class="col-12 col-md">
                <?= Yii::$app->formatter->asEmail($model->email) ?>
            </div>
        </div>

        <?php if ($model->telegram_chat_id) : ?>

        <div class="row border-top py-2">
            <div class="col-12 col-md-auto col-name text-bold">
                <?= $model->getAttributeLabel('telegram_chat_id') ?>
            </div>
            <div class="col-12 col-md">
                <?= Html::encode($model->telegram_chat_id) ?>
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
