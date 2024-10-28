<?php

/** @var app\models\DocumentEvent $model */
/** @var integer $index */
/** @var bool $isLast */
/** @var yii\data\DataProviderInterface $dataProvider */

use app\widgets\AvatarWidget;
use yii\bootstrap5\Html;
?>

<div class="d-flex align-items-start">

    <?= AvatarWidget::widget([
        'name' => Html::encode($model->user->getEmployeeFullName()),
        'size' => 36,
        'imgClass' => 'rounded-circle me-2',
    ]) ?>

    <div class="flex-grow-1">
        <small class="float-end text-navy"><?= Yii::$app->formatter->asDatetime($model->created_at); ?></small>
        <strong><?= Html::encode($model->user->getEmployeeFullName()) ?></strong> <?= Html::encode($model->event) ?>

        <div class="border text-sm text-muted p-2 mt-1">

            <?= Html::a(Html::encode($model->document->name), ['document/view', 'id' => $model->document_id], ['target' => '_blank']) ?>

        </div>
    </div>
</div>

<?php if (!$isLast): ?>

    <hr>

<?php endif; ?>