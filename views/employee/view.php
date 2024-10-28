<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

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

        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-striped table-bordered detail-view mb-0'],
            'attributes' => [
                [
                    'attribute' => 'last_name',
                    'captionOptions' => ['width' => '170px'],
                ],
                'first_name',
                'middle_name',
                'birth_date',
                [
                    'attribute' => 'user_id',
                    'captionOptions' => ['width' => '170px'],
                    'value' => $model->user ? $model->user->username : null,
                ],
                [
                    'attribute' => 'position_id',
                    'captionOptions' => ['width' => '170px'],
                    'value' => $model->position ? $model->position->name : null,
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
