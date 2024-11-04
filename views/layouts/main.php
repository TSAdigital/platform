<?php
/** @var yii\web\View $this */
/** @var string $content */

use app\assets\ToastAsset;
use app\widgets\ToastWidget;
use yii\helpers\Html;
use app\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
ToastAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'sizes' => 'any', 'href' => Yii::getAlias('@web/favicon.ico')]);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/svg+xml', 'sizes' => 'any', 'href' => Yii::getAlias('@web/favicon.svg')]);
$this->registerJs("
    $(document).on('pjax:end', function() {
        var siteName = '" . Yii::$app->params['siteName'] . "';
        var pageTitle = document.title.split(' - ')[0];
        document.title = siteName + ' - ' + pageTitle;
    });
");
?>

<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Yii::$app->params['siteName'] ?> - <?= Html::encode($this->title) ?></title>

    <meta name="theme-color" content="#f5f7fb">
    <meta name="msapplication-config" content="<?= Url::to('@web/browserconfig.xml') ?>" />
    <link rel="manifest" href="<?= Url::to('@web/manifest.json') ?>">
    <link rel="apple-touch-icon" sizes="57x57" href="<?= Url::to('@web/images/icons/apple-icon-57x57.png') ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= Url::to('@web/images/icons/apple-icon-60x60.png') ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= Url::to('@web/images/icons/apple-icon-72x72.png') ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= Url::to('@web/images/icons/apple-icon-76x76.png') ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= Url::to('@web/images/icons/apple-icon-114x114.png') ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= Url::to('@web/images/icons/apple-icon-120x120.png') ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= Url::to('@web/images/icons/apple-icon-144x144.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= Url::to('@web/images/icons/apple-icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= Url::to('@web/images/icons/apple-icon-180x180.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?= Url::to('@web/images/icons/android-icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= Url::to('@web/images/icons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= Url::to('@web/images/icons/favicon-96x96.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= Url::to('@web/images/icons/favicon-16x16.png') ?>">
    <meta name="msapplication-TileColor" content="#f5f7fb">
    <meta name="msapplication-TileImage" content="<?= Url::to('@web/images/icons/ms-icon-144x144.png') ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="TSA platform">

    <?php $this->head() ?>

</head>
<body>

<?php $this->beginBody() ?>

<div id="loading-overlay"></div>

<?= ToastWidget::widget(); ?>

<div class="wrapper">

    <?= $this->render('_sidebar') ?>

    <div class="main">

        <?= $this->render('_navbar') ?>

        <?php if ($this->context->getRoute() != 'site/profile') : ?>

        <?= $this->render('_content', ['content' => $content]) ?>

        <?php else : ?>

        <?= $this->render('_profile', ['content' => $content]) ?>

        <?php endif; ?>

        <?= $this->render('_footer') ?>

    </div>
</div>

<?php $this->endBody() ?>

</body>
</html>

<?php $this->endPage() ?>
