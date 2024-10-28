<?php

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Новый пароль для: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Новый пароль';
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form_password', [
            'model' => $model,
        ]) ?>

    </div>
</div>
