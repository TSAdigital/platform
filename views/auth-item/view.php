<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

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

        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
            'attributes' => [
                [
                    'attribute' => 'description',
                    'label' => 'Наименование',
                    'captionOptions' => ['width' => '170px'],
                ],
                [
                    'attribute' => 'status',
                    'label' => 'Статус',
                    'value' => $model->getStatusName()
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'datetime',
                    'label' => 'Запись создана',
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => 'datetime',
                    'label' => 'Запись обновлена',
                ],
            ],
        ]) ?>

    </div>
</div>
