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
                    'label' => 'Документы',
                    'url' => ['document/index'],
                    'icon' => 'file-text',
                    'active' => $this->context->getUniqueId() == 'document',
                    'visible' => Yii::$app->user->can('viewDocumentMenu'),
                ],
                [
                    'label' => 'Помощь',
                    'url' => ['site/help'],
                    'icon' => 'help-circle',
                    'active' => $this->context->getRoute() == 'site/help'
                ],
                [
                    'label' => 'О проекте',
                    'url' => ['site/about'],
                    'icon' => 'info',
                    'active' => $this->context->getRoute() == 'site/about',
                    'visible' => Yii::$app->user->can('administrator'),
                ],
                [
                    'header' => 'Справочники',
                    'visible' => in_array(true, [
                        Yii::$app->user->can('viewPositionMenu'),
                        Yii::$app->user->can('viewEmployeeMenu'),
                    ], true)
                ],
                [
                    'label' => 'Должности',
                    'url' => ['position/index'],
                    'icon' => 'grid',
                    'active' => $this->context->getUniqueId() == 'position',
                    'visible' => Yii::$app->user->can('viewPositionMenu'),
                ],
                [
                    'label' => 'Сотрудники',
                    'url' => ['employee/index'],
                    'icon' => 'user',
                    'active' => $this->context->getUniqueId() == 'employee',
                    'visible' => Yii::$app->user->can('viewEmployeeMenu'),
                ],
                [
                    'header' => 'Администрирование',
                    'visible' => in_array(true, [
                        Yii::$app->user->can('viewUserMenu'),
                        Yii::$app->user->can('viewRoleMenu')
                    ], true)
                ],
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