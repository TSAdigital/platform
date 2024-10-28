<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

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

        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
            'attributes' => [
                [
                    'attribute' => 'username',
                    'captionOptions' => ['width' => '170px'],
                ],
                [
                    'attribute' => 'role',
                    'value' => $model->getRoleName()
                ],
                'email:email',
                [
                    'attribute' => 'telegram_chat_id',
                    'visible' => $model->telegram_chat_id != null,
                ],
                [
                    'attribute' => 'status',
                    'value' => $model->getStatusName()
                ],
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>

    </div>
</div>
