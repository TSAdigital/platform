<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Issuer $model */

$this->title = 'Новый удостоверяющий центр';
$this->params['breadcrumbs'][] = ['label' => 'Удостоверяющие центры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>

    </div>
</div>
