<?php
use app\widgets\AvatarWidget;
use yii\bootstrap5\Html;

/** @var app\models\DocumentEvent $model */
/** @var integer $index */
/** @var bool $isLast */
/** @var yii\data\DataProviderInterface $dataProvider */
?>

<div class="d-flex align-items-start py-2">
    <div class="flex-shrink-0 me-3">
        <?= AvatarWidget::widget([
            'name' => Html::encode($model->user->getEmployeeFullName()),
            'size' => 42,
            'imgClass' => 'rounded-circle shadow-sm',
        ]) ?>
    </div>

    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <strong class="text-primary"><?= Html::encode($model->user->getEmployeeFullName()) ?></strong>
            <small class="text-muted"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></small>
        </div>

        <p class="mb-2"><?= Html::encode($model->event) ?></p>

        <div class="rounded bg-light mb-0">
            <div class="p-2">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-2">
                        <svg data-feather="file-text"></svg>
                    </div>
                    <div>
                        <h6 class="mb-0"><?= Html::a(
                                Html::encode($model->document->name),
                                ['document/view', 'id' => $model->document_id],
                                [
                                    'class' => 'text-decoration-none',
                                    'target' => '_blank'
                                ]
                            ) ?></h6>
                        <small class="text-muted">
                            <?= Yii::$app->formatter->asDatetime($model->document->created_at) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>