<?php

/** @var yii\web\View $this */
/** @var string $telegramBotUrl */
/** @var QrCodeGenerator $qrCodeGenerator */
/** @var DocumentEvent $eventDataProvider */
/** @var Certificate $certificates */
/** @var array $groupedDocuments */

use app\components\QrCodeGenerator;
use app\models\Certificate;
use app\models\DocumentEvent;
use app\widgets\AvatarWidget;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\web\JqueryAsset;
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
$this->registerCssFile(Yii::getAlias('@web/css/cropper.min.css'));
$this->registerJsFile(Yii::getAlias('@web/js/cropper.min.js'), [
    'depends' => [JqueryAsset::class]
]);
?>

<div class="site-profile">
    <div class="row">
        <div class="col-md-5 col-xl-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">Детали профиля</h5>
                </div>
                <div class="card-body text-center">

                    <div id="avatar-container" style="cursor: pointer;">
                        <?= AvatarWidget::widget([
                            'name' => Html::encode(Yii::$app->user->identity->getEmployeeFullName()),
                            'avatarUrl' => Yii::$app->user->identity->avatar ? '/uploads/avatars/' . Yii::$app->user->identity->avatar : null,
                            'size' => 128,
                            'imgClass' => 'img-fluid rounded-circle mb-2',
                            'userId' => Yii::$app->user->id
                        ]) ?>
                        <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
                    </div>

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
                            <h4 class="modal-title" id="telegramLabel">Подключить телеграм</h4>
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

            <?php if ($groupedDocuments): ?>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title">Зарегистрированные документы в РЭМД</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="nav flex-column nav-pills" id="yearTabs" role="tablist" aria-orientation="vertical">

                                <?php $firstYear = true; ?>
                                <?php foreach ($groupedDocuments as $year => $yearData): ?>

                                    <button class="nav-link text-start <?= $firstYear ? 'active' : '' ?>"
                                            id="year-<?= $year ?>-tab"
                                            data-bs-toggle="pill"
                                            data-bs-target="#year-<?= $year ?>"
                                            type="button"
                                            role="tab"
                                            aria-controls="year-<?= $year ?>"
                                            aria-selected="<?= $firstYear ? 'true' : 'false' ?>">
                                        <?= $year ?>
                                    </button>

                                    <?php $firstYear = false; ?>
                                <?php endforeach; ?>

                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content" id="yearTabsContent">

                                <?php $firstYear = true; ?>
                                <?php foreach ($groupedDocuments as $year => $yearData): ?>

                                    <div class="tab-pane fade <?= $firstYear ? 'show active' : '' ?>"
                                         id="year-<?= $year ?>"
                                         role="tabpanel"
                                         aria-labelledby="year-<?= $year ?>-tab">

                                        <!-- Аккордеон месяцев -->
                                        <div class="accordion" id="monthAccordion-<?= $year ?>">

                                            <?php $firstMonth = true; ?>
                                            <?php foreach ($yearData['months'] as $month => $monthData): ?>

                                                <div class="accordion-item mb-2">
                                                    <h2 class="accordion-header" id="month-heading-<?= $year ?>-<?= $month ?>">
                                                        <button class="accordion-button <?= !$firstMonth ? 'collapsed' : '' ?>"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#month-collapse-<?= $year ?>-<?= $month ?>"
                                                                aria-expanded="<?= $firstMonth ? 'true' : 'false' ?>">
                                                            <?= $monthData['name'] ?>
                                                            <span class="badge bg-primary ms-2"><?= $monthData['count'] ?></span>
                                                        </button>
                                                    </h2>

                                                    <div id="month-collapse-<?= $year ?>-<?= $month ?>"
                                                         class="accordion-collapse collapse <?= $firstMonth ? 'show' : '' ?>"
                                                         aria-labelledby="month-heading-<?= $year ?>-<?= $month ?>"
                                                         data-bs-parent="#monthAccordion-<?= $year ?>">
                                                        <div class="accordion-body">

                                                            <?php
                                                            $typesCount = count($monthData['types']);
                                                            $currentIndex = 0;
                                                            foreach ($monthData['types'] as $type => $count):
                                                                $currentIndex++;
                                                                $marginClass = ($currentIndex < $typesCount) ? 'mb-2' : '';
                                                                ?>

                                                                <div class="d-flex justify-content-between align-items-center <?= $marginClass ?>">
                                                                    <span><?= Html::encode($type) ?></span>
                                                                    <span class="fw-bold me-1"><?= $count ?></span>
                                                                </div>

                                                            <?php endforeach; ?>

                                                        </div>
                                                    </div>
                                                </div>

                                                <?php $firstMonth = false; ?>
                                            <?php endforeach; ?>

                                        </div>
                                    </div>

                                    <?php $firstYear = false; ?>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title">Активность</h5>
                </div>
                <div class="card-body p-0">

                    <?= ListView::widget([
                        'dataProvider' => $eventDataProvider,
                        'emptyText' => '<div class="text-center p-4 text-muted">
                              <svg data-feather="info" class="mb-2 text-primary" style="width: 48px; height: 48px;"></svg>
                              <p class="mb-0">На данный момент нет активности</p>
                              <small>Пожалуйста, проверяйте обновления позже</small>
                           </div>',
                        'itemView' => function ($model, $key, $index, $widget) {
                            $isLast = ($index === $widget->dataProvider->getCount() - 1);
                            return $this->render('_event_list', [
                                'model' => $model,
                                'isLast' => $isLast
                            ]);
                        },
                        'layout' => '<div class="list-group list-group-flush">{items}</div>',
                        'itemOptions' => ['class' => 'list-group-item list-group-custom'],
                    ]) ?>

                    <?php if ($eventDataProvider->pagination->getPageCount() > 1) : ?>

                        <div class="card-footer border-top">

                            <?= LinkPager::widget([
                                'pagination' => $eventDataProvider->pagination,
                                'options' => ['class' => 'pagination justify-content-center mb-0'],
                                'maxButtonCount' => 6,
                            ]) ?>

                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="avatar-modal" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="accessLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="accessLabel">Предоставить доступ</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="image-to-crop" src="" alt="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" tabindex="-1" aria-label="Close">Отмена</button>
                <button type="button" class="btn btn-primary" id="crop-avatar">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
$(document).ready(function() {
    var avatarContainer = $('#avatar-container');
    var avatarUpload = $('#avatar-upload');
    var avatarModal = $('#avatar-modal');
    var imageToCrop = $('#image-to-crop');
    var cropButton = $('#crop-avatar');
    var cropper;
    
    avatarContainer.on('click', function(e) {
        if (e.target !== avatarUpload[0]) {
            avatarUpload.trigger('click');
        }
    });
    
    avatarUpload.on('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                
                imageToCrop.attr('src', e.target.result);
                avatarModal.modal('show');
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    
    avatarModal.on('shown.bs.modal', function() {
        cropper = new Cropper(imageToCrop[0], {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 0.8,
            responsive: true,
            guides: false
        });
    }).on('hidden.bs.modal', function() {
        avatarUpload.val('');
        
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });
    
    cropButton.on('click', function() {
        if (!cropper) return;
        
        cropper.getCroppedCanvas({
            width: 256,
            height: 256,
            fillColor: '#fff'
        }).toBlob(function(blob) {
            var formData = new FormData();
            formData.append('avatar', blob, 'avatar.jpg');
            
            $.ajax({
                url: '/user/upload-avatar',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('img[data-user-id="' + response.userId + '"]').attr('src', response.avatarUrl + '?t=' + Date.now());

                        avatarModal.modal('hide');
                    } else {
                        alert(response.error || 'Ошибка загрузки');
                    }
                },
                error: function() {
                    alert('Ошибка соединения');
                }
            });
        }, 'image/jpeg', 0.9);
    });
});
JS;
$this->registerJs($js);
?>


