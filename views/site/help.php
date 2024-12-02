<?php

/** @var yii\web\View $this */
/** @var string $telegramSupport */
/** @var object $qrCodeGenerator */

use yii\helpers\Html;

$this->title = 'Помощь';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">
        <h3>Техническая поддержка</h3>
        <p class="mb-0">Если у вас возникли вопросы, связанные с работой нашего сервиса, вы можете обратиться в техническую поддержку:</p>
        <p class="mb-0">Специалист: <?= Yii::$app->params['specialist_support'] ?></p>
        <p class="mb-0">Номер телефона: <?= Yii::$app->params['phone_support'] ?></p>

        <?php if($telegramSupport) : ?>

        <p class="mb-0">Telegram: <a href="#" data-bs-toggle="modal" data-bs-target="#telegram">@maksimov</a></p>

        <div class="modal fade" id="telegram" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="staticBackdropLabel">Телеграм технической поддержки</h4>
                        <button type="button" class="btn-close" tabindex="-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center mb-0">Используйте камеру вашего телефона для сканирования QR-кода или перейдите по ссылке.</p>
                        <p class="text-center mb-0"><?= Html::img($qrCodeGenerator->generateBase64($telegramSupport), ['alt' => 'QR Code']);?></p>

                        <?= Html::a('Перейти по ссылке', $telegramSupport, ['id' => 'link-to-connect', 'class' => 'btn btn-primary w-100', 'target' => '_blank']) ?>

                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <?php if(Yii::$app->params['telegram']) : ?>

        <p class="mb-0 mt-2">Подпишитесь на нашего Telegram-бота в <?= Html::a('личном кабинете', ['site/profile']) ?> для удобного получения уведомлений о событиях в реальном времени.</p>

        <?php endif; ?>

    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3>Инструкция по работе с документами</h3>
        <p class="mb-2">Видеоинструкцию можно <?= Html::a('<strong>посмотреть</strong>', '#!', ['class' => 'video-link', 'data-video' => 'doc.mp4', 'data-title' => 'Видеоинструкция - работа с документами']) ?> на сайте или <?= Html::a('<strong>скачать</strong>', '@web/video/doc.mp4', ['target' => '_blank']) ?> и посмотреть на устройстве.</p>
        <div id="video-container"></div>
        <h4 class="mb-1">Создание нового документа</h4>
        <ol>
            <li>
                Перейдите в раздел <?= Html::a('<strong>Документы</strong>', ['document/index'], ['target' => '_blank']) ?>.
            </li>
            <li>
                Нажмите кнопку <?= Html::a('<strong>Добавить</strong>', ['document/create'], ['target' => '_blank']) ?>, чтобы начать процесс создания нового документа.
            </li>
            <li>
                Заполните поля формы:
                <ul>
                    <li><strong>Наименование документа:</strong> Введите название вашего документа. Это поле обязательно для заполнения.</li>
                    <li><strong>Описание (по необходимости):</strong> Введите дополнительную информацию о документе. Это поле не является обязательным, но рекомендуется заполнять его для полноты информации.</li>
                </ul>
            </li>
            <li>
                После заполнения всех необходимых полей, нажмите кнопку <strong>Сохранить</strong>, чтобы создать документ. Система автоматически перенаправит вас на страницу созданного документа.
            </li>
        </ol>

        <h4 class="mb-1">Редактирование документа</h4>
        <ol>
            <li>
                Перейдите в раздел <?= Html::a('<strong>Документы</strong>', ['document/index'], ['target' => '_blank']) ?> и выберите нужный документ из списка.
            </li>
            <li>
                Нажмите кнопку <strong>Редактировать</strong>, чтобы перейти в режим редактирования.
            </li>
            <li>
                Измените данные документа по мере необходимости.
            </li>
            <li>
                После внесения изменений нажмите кнопку <strong>Сохранить</strong>, чтобы обновить документ.
            </li>
        </ol>

        <h4 class="mb-1">Управление доступом</h4>
        <ol>
            <li>
                Внутри документа найдите вкладку <strong>Доступ</strong>, которая находится в верхней части страницы.
            </li>

            <li>
                Нажмите на иконку <?= Html::tag('svg', '', ['class' => 'align-middle text-success', 'data-feather' => 'plus-circle']) ?> или на кнопку <strong>Предоставить доступ</strong> для выбора сотрудников, которым следует предоставить доступ к документу.
            </li>
        </ol>

        <h4 class="mb-1">Добавление файлов</h4>
        <ol>
            <li>
                Внутри документа найдите вкладку <strong>Файлы</strong>, которая находится в верхней части страницы.
            </li>
            <li>
                Нажмите на иконку <?= Html::tag('svg', '', ['class' => 'align-middle text-success', 'data-feather' => 'plus-circle']) ?> или на кнопку <strong>Добавить файлы</strong>, выберите файл на вашем устройстве, который вы хотите добавить.
            </li>
            <li>
                Нажмите на кнопку <strong>Загрузить</strong> для загрузки файла.
            </li>
        </ol>

        <h4 class="mb-1">Публикация документа</h4>
        <ol class="mb-0">
            <li>
                В меню документа найдите кнопку <strong>Опубликовать</strong> и нажмите на неё. Это действие завершит процесс работы с документом и он станет доступен выбранным сотрудникам.
            </li>
        </ol>
    </div>
</div>

<div class="modal fade" id="videoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="videoModalLabel">Видеоинструкция</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" tabindex="-1" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="embed-responsive embed-responsive-16by9 mb-3" style="max-width: 100%; overflow: hidden;">
                    <?php if (!file_exists(Yii::getAlias('@webroot') . '/video/doc.mp4')) : ?>
                        <div class="h4 my-3 text-center">В папке @web/video отсутствует видеофайл. Вы можете <?= Html::a('скачать', 'https://platform.tsa-digital.ru/video/doc.mp4') ?> его по следующей ссылке и разместить в указанной папке под названием doc.mp4</div>
                    <?php endif; ?>
                    <video id="myVideo" class="embed-responsive-item" controls style="width: 100%; height: auto;">
                        <source src="" type="video/mp4">
                        Ваш браузер не поддерживает воспроизведение видео.
                    </video>
                </div>
                <div class="video-timestamps" data-video="doc.mp4" style="display: none;">
                    <p class="timestamps mb-0" data-time="6">00:06 - Вход в систему</p>
                    <p class="timestamps mb-0" data-time="59">00:59 - Раздел помощь</p>
                    <p class="timestamps mb-0" data-time="84">01:24 - Создание документа</p>
                    <p class="timestamps mb-0" data-time="124">02:04 - Доступ к документу</p>
                    <p class="timestamps mb-0" data-time="171">02:51 - Загрузка файлов</p>
                    <p class="timestamps mb-0" data-time="219">03:39 - Публикация</p>
                    <p class="timestamps mb-0" data-time="277">04:37 - Заключение</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<< JS
    jQuery(function ($) {
        
        const setTimestampsVisibility = (videoFile) => {
            document.querySelectorAll('.video-timestamps').forEach(div => {
                div.style.display = div.getAttribute('data-video') === videoFile ? 'block' : 'none';
                if (div.style.display === 'block') {
                    div.querySelectorAll('.timestamps').forEach(timestamp => {
                        timestamp.onclick = (event) => {
                            event.preventDefault();
                            seekVideo(timestamp.getAttribute('data-time'));
                        };
                    });
                }
            });
        };

        const seekVideo = (seconds) => {
            const video = document.getElementById('myVideo');
            video.currentTime = seconds;
            video.play();
        };

        const videoModal = $('#videoModal');

        $('.video-link').on('click', function() {
            const videoFile = $(this).data('video');
            const title = $(this).data('title');
            const video = videoModal.find('video').get(0);

            if (videoFile) {
                video.src = '/video/' + videoFile;
                setTimestampsVisibility(videoFile);
            }
            
            $('#videoModalLabel').text(title);
            videoModal.modal('show');
        });

        videoModal.on('hidden.bs.modal', function() {
            const video = $(this).find('video').get(0);
            if (video) {
                video.pause();
                video.currentTime = 0;
                video.src = '';
            }
        });

        videoModal.on('shown.bs.modal', function() {
            $(this).find('video').get(0).play();
        });

    });
JS;
$this->registerJs($script);
?>