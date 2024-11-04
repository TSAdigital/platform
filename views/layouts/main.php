<?php
/** @var yii\web\View $this */
/** @var string $content */

use app\assets\ToastAsset;
use app\widgets\ToastWidget;
use yii\helpers\Html;
use app\assets\AppAsset;

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
