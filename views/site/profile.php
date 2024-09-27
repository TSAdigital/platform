<?php

/** @var yii\web\View $this */

use app\widgets\AvatarWidget;
use yii\helpers\Html;

$this->title = 'Профиль';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-profile">
    <div class="row">
        <div class="col-md-4 col-xl-3">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Детали профиля</h5>
                </div>
                <div class="card-body text-center">

                    <?= AvatarWidget::widget([
                        'name' => Yii::$app->user->isGuest ? 'Гость' : Html::encode(Yii::$app->user->identity->username),
                        'size' => 128,
                        'img_class' => 'img-fluid rounded-circle mb-2',
                    ]) ?>

                    <?php if (isset(Yii::$app->user->identity->username)) : ?>

                    <h5 class="card-title mb-0"><?= Html::encode(Yii::$app->user->identity->username) ?></h5>

                    <?php endif; ?>
                    <?php if (isset(Yii::$app->user->identity->roleName)) : ?>

                    <div class="text-muted mb-2"><?= Html::encode(Yii::$app->user->identity->getRoleName()) ?></div>

                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="col-md-8 col-xl-9">
            <div class="card">
                <div class="card-header">

                    <h5 class="card-title mb-0">Активность</h5>
                </div>
                <div class="card-body h-100">
                    На данный момент нет активности. Пожалуйста, проверяйте обновления позже.
                </div
            </div>
        </div>
    </div>
</div>
