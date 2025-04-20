<?php

/** @var yii\web\View $this */
/** @var app\models\Certificate $certificatesDataProvider */
/** @var integer $totalCertificates */
/** @var integer $activeCertificates */
/** @var integer $inactiveCertificates */

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Аналитика';
$this->params['breadcrumbs'][] = ['label' => 'Сертификаты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Всего</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'pocket']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= $totalCertificates ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Активные</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'check-circle']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= $activeCertificates ?></h1>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <h5 class="card-title">Аннулированные</h5>
                    </div>

                    <div class="col-auto">
                        <div class="stat text-primary">
                            <?= Html::tag('svg', '', ['data-feather' => 'x-circle']) ?>
                        </div>
                    </div>
                </div>
                <h1 class="mt-1 mb-0"><?= $inactiveCertificates ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
            <div class="row text-bold">
                <div class="col-auto text-center py-2 fixed-column fw-bold">#</div>
                <div class="col py-2 fw-bold justify-content-center align-self-center">Сертификат</b></div>
                <div class="col-xl-2 d-none d-xl-block py-2 fw-bold justify-content-center align-self-center">Издатель</div>
                <div class="col-md-2 d-none d-md-block py-2 fw-bold text-center justify-content-center align-self-center">Действует с</div>
                <div class="col-md-2 d-none d-md-block py-2 fw-bold text-center justify-content-center align-self-center">Действует по</div>
                <div class="col-md-2 d-none d-md-block py-2 fw-bold text-center justify-content-center align-self-center">Статус</div>

                <?= ListView::widget([
                    'dataProvider' => $certificatesDataProvider,
                    'emptyText' => '<div class="pt-2 border-top">Нет данных для отображения.</div>',
                    'itemView' => function ($model, $key, $index, $widget) {
                        $currentPage = $widget->dataProvider->pagination->page;
                        $pageSize = $widget->dataProvider->pagination->pageSize;
                        $rowNumber = $index + 1 + ($currentPage * $pageSize);

                        return $this->render('_certificates_list', [
                            'model' => $model,
                            'index' => $rowNumber
                        ]);
                    },
                    'layout' => "{items}",
                ]);
                ?>

            </div>
            <div class="pagination-container">

                <?php if ($certificatesDataProvider->pagination->getPageCount() > 1) : ?>

                    <?= LinkPager::widget([
                        'pagination' => $certificatesDataProvider->pagination,
                        'options' => ['class' => 'mt-3'],
                        'maxButtonCount' => 6,
                    ]) ?>

                <?php endif; ?>

            </div>
    </div>
</div>