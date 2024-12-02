<?php

/** @var yii\web\View $this */
/** @var string $telegramBotUrl */
/** @var QrCodeGenerator $qrCodeGenerator */
/** @var DocumentEvent $eventDataProvider */
/** @var Certificate $certificates */

use app\components\QrCodeGenerator;
use app\models\Certificate;
use app\models\DocumentEvent;
use app\widgets\AvatarWidget;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Профиль';
$this->params['breadcrumbs'][] = $this->title;
$script = <<< JS
    $(document).ready(function() {
        $('.modalTelegram').click(function() {
            $('#telegram').modal('show');
        });
        $('#link-to-connect').click(function() {
            $('#telegram').modal('hide');
        });
    });
JS;
$this->registerJs($script);
?>

<div class="site-profile">
    <div class="row">
        <div class="col-md-5 col-xl-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">Детали профиля</h5>
                </div>
                <div class="card-body text-center">

                    <?= AvatarWidget::widget([
                        'name' => Html::encode(Yii::$app->user->identity->getEmployeeFullName()),
                        'size' => 128,
                        'imgClass' => 'img-fluid rounded-circle mb-2',
                    ]) ?>

                    <?php if (isset(Yii::$app->user->identity->username)) : ?>

                    <h5 class="card-title"><?= Html::encode(Yii::$app->user->identity->getEmployeeFullName())?></h5>

                    <?php endif; ?>
                    <?php if (isset(Yii::$app->user->identity->roleName)) : ?>

                    <div class="text-muted mb-2"><?= Html::encode(Yii::$app->user->identity->employee ? Yii::$app->user->identity->employee->position->name : Yii::$app->user->identity->getRoleName()) ?></div>

                    <?php endif; ?>

                </div>
            </div>

            <?php if (Yii::$app->user->identity->telegram_chat_id == null && Yii::$app->params['telegram'] ) : ?>

            <?= Html::a('Подключить телеграм', '#', ['class' => 'btn btn-primary w-100 modalTelegram mb-3']) ?>

            <div class="modal fade" id="telegram" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="telegramLabel" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="telegramLabel">Подключить телеграм</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-center mb-0">Используйте камеру вашего телефона для сканирования QR-кода или перейдите по ссылке.</p>
                            <p class="text-center mb-0"><?= Html::img($qrCodeGenerator->generateBase64($telegramBotUrl), ['alt' => 'QR Code']);?></p>

                            <?= Html::a('Подключить по ссылке', $telegramBotUrl, ['id' => 'link-to-connect', 'class' => 'btn btn-primary w-100', 'target' => '_blank']) ?>

                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>

            <?php if($certificates) : ?>

            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h5 class="card-title">Сертификат электронной подписи</h5>
                </div>
                <div class="card-body">

                    <?php foreach ($certificates as $certificate) : ?>

                        <p class="mb-0"><b>Владелец:</b> <?= $certificate->employee->getFullName() ?></p>

                        <?php if ($certificate->serial_number) : ?>

                        <p class="mb-0 text-truncate"><b>Серийный номер:</b> <?= $certificate->serial_number ?></p>

                        <?php endif; ?>

                        <p class="mb-0"><b>Издатель:</b> <?= $certificate->issuer->name ?></p>
                        <p class="mb-0"><b>Действует:</b> с <?= $certificate->valid_from ?> по <?= $certificate->valid_to ?></p>

                    <?php endforeach; ?>

                </div>
            </div>

            <?php endif; ?>

        </div>

        <div class="col-md-7 col-xl-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title">Активность</h5>
                </div>
                <div class="card-body h-100">

                    <?= ListView::widget([
                        'dataProvider' => $eventDataProvider,
                        'emptyText' => 'На данный момент нет активности. Пожалуйста, проверяйте обновления позже.',
                        'itemView' => function ($model, $key, $index, $widget) {
                            $currentPage = $widget->dataProvider->pagination->page;
                            $pageSize = $widget->dataProvider->pagination->pageSize;
                            $rowNumber = $index + 1 + ($currentPage * $pageSize);
                            $isLast = ($index === $widget->dataProvider->getCount() - 1);

                            return $this->render('_event_list', [
                                'model' => $model,
                                'index' => $rowNumber,
                                'isLast' => $isLast
                            ]);
                        },
                        'layout' => "{items}",
                    ]) ?>

                    <?php if ($eventDataProvider->pagination->getPageCount() > 1) : ?>

                        <?= LinkPager::widget([
                            'pagination' => $eventDataProvider->pagination,
                            'options' => ['class' => 'mt-3'],
                        ]) ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
