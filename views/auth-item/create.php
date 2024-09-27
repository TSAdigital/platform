<?php

/** @var yii\web\View $this */
/** @var app\models\AuthItem $model */

$this->title = 'Новая роль';
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="auth-item-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
