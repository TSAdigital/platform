<?php
/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use app\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>

<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="manifest" href="<?= Url::to('@web/manifest.json') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= Url::to('@web/images/icons/icon180.png') ?>">

    <?php $this->head() ?>

</head>
<body>

<?php $this->beginBody() ?>

<main class="d-flex w-100">
    <div class="container d-flex flex-column">
        <div class="row vh-100">
            <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                <div class="d-table-cell align-middle">

                    <?= $content ?>

                </div>
            </div>
        </div>
    </div>
</main>

<?php $this->endBody() ?>

</body>
</html>

<?php $this->endPage() ?>
