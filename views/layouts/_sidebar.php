<?php

use app\widgets\SidebarMenu;
?>

<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="<?= Yii::$app->homeUrl ?>">
            <span class="align-middle"><?= Yii::$app->params['siteName']; ?><sup><small><?= Yii::$app->params['siteDescription']; ?></small></sup></span>
        </a>

        <?= SidebarMenu::widget([
            'items' => [
                ['header' => 'Навигация'],
                [
                    'label' => 'О проекте',
                    'url' => ['site/about'],
                    'icon' => 'info',
                    'active' => $this->context->getRoute() == 'site/about'
                ],

                [
                    'header' => 'Администрирование',
                    'visible' => in_array(true, [
                        Yii::$app->user->can('viewUserMenu'),
                        Yii::$app->user->can('viewRoleMenu')
                    ], true)],
                [
                    'label' => 'Пользователи',
                    'url' => ['user/index'],
                    'icon' => 'users',
                    'active' => $this->context->getUniqueId() == 'user',
                    'visible' => Yii::$app->user->can('viewUserMenu'),
                ],
                [
                    'label' => 'Роли',
                    'url' => ['auth-item/index'],
                    'icon' => 'lock',
                    'active' => $this->context->getUniqueId() == 'auth-item',
                    'visible' => Yii::$app->user->can('viewRoleMenu'),
                ]
            ]
        ]) ?>

    </div>
</nav>