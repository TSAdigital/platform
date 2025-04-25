<?php

/** @var yii\web\View $this */
/** @var array $allDocTypes */
/** @var object $settings */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Настройки отображения документов РЭМД';
$this->params['breadcrumbs'][] = ['label' => 'Зарегистрированные документы в РЭМД', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">

        <?php if ($allDocTypes) : ?>

        <?php $form = ActiveForm::begin(); ?>

        <div class="form-group">
            <label class="form-label">Выберите виды документов для отображения по умолчанию:</label>

            <div class="doc-types-list">
                <?php foreach ($allDocTypes as $type): ?>
                    <div class="form-check d-flex align-items-center">
                        <?= Html::checkbox(
                            'doc_types[]',
                            in_array($type, $settings->getEnabledDocTypesArray()),
                            [
                                'value' => $type,
                                'id' => 'type_' . md5($type),
                                'class' => 'form-check-input'
                            ]
                        ) ?>
                        <label class="form-check-label ms-2" for="<?= 'type_' . md5($type) ?>">
                            <?= Html::encode($type) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

        <div class="form-group mt-3">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <?php else: ?>

        <p class="mb-0">Нет данных для отображения</p>

        <?php endif; ?>

    </div>
</div>

<?php
$this->registerCss(<<<CSS
.form-check {
    margin-bottom: 8px;
}

.form-check-input{
    margin-top: -2px !important;
}
CSS
);
?>