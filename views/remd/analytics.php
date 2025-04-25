<?php

/** @var array $data */
/** @var array $year */
/** @var array $typedData */

use yii\helpers\Html;

$this->title = 'Аналитика по документам зарегистрированных в РЭМД';
$this->params['breadcrumbs'][] = ['label' => 'Зарегистрированные документы в РЭМД', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-xl-row align-items-start">
            <div class="nav flex-column nav-pills me-xl-3 mb-3 col-xl-2 col-12" id="remd-tabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link active text-start" id="general-tab" data-bs-toggle="pill"
                        data-bs-target="#general" type="button" role="tab"
                        aria-controls="general" aria-selected="true">
                    Все документы
                </button>

                <?php foreach ($data['typed'] as $index => $item): ?>
                    <?php if (!empty($item['type'])): ?>

                        <button class="nav-link text-start" id="type-<?= $index ?>-tab" data-bs-toggle="pill"
                                data-bs-target="#type-<?= $index ?>" type="button" role="tab"
                                aria-controls="type-<?= $index ?>" aria-selected="false">
                            <?= Html::encode($item['type']) ?>
                        </button>

                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="tab-content flex-grow-1 pe-xl-3 col-xl-10 col-12">
                <div class="tab-pane fade show active" id="general" role="tabpanel"
                     aria-labelledby="general-tab" tabindex="0">
                    <h3>Все документы</h3>

                    <?= $this->render('_infographic', [
                        'plan' => $data['general']['plan'],
                        'actual' => $data['general']['actual'],
                        'data' => $data
                    ]) ?>

                </div>

                <?php foreach ($data['typed'] as $index => $item): ?>
                    <?php if (!empty($item['type'])): ?>

                        <div class="tab-pane fade" id="type-<?= $index ?>" role="tabpanel"
                             aria-labelledby="type-<?= $index ?>-tab" tabindex="0">
                            <h3><?= Html::encode($item['type']) ?></h3>
                            <?= $this->render('_infographic', [
                                'plan' => $item['plan'],
                                'actual' => $item['actual'],
                                'data' => $data
                            ]) ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <?= $this->render('_summary_table', [
            'plan' => $data['general']['plan'],
            'actual' => $data['general']['actual'],
            'typedData' => $data['typed'],
            'data' => $data
        ]) ?>
    </div>
</div>