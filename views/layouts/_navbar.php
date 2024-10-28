<?php

use app\widgets\AvatarWidget;
use yii\bootstrap5\Html;
?>

<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav navbar-align">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">

                    <?= AvatarWidget::widget(['name' => Html::encode(Yii::$app->user->identity->getEmployeeFullName())]) ?>

                    <span class="text-dark">

                        <?= Html::encode(Yii::$app->user->identity->getEmployeeFullName()) ?>

                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">

                    <?= Html::a('<i class="align-middle me-1" data-feather="user"></i> Профиль', ['site/profile'], ['class' => 'dropdown-item']) ?>

                    <div class="dropdown-divider"></div>

                    <?= Html::beginForm(['/site/logout']) ?>
                    <?= Html::submitButton('<i class="align-middle me-1" data-feather="log-out"></i> Выйти', ['class' => 'dropdown-item']) ?>
                    <?= Html::endForm() ?>

                </div>
            </li>
        </ul>
    </div>
</nav>
