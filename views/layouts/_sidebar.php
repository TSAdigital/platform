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
                    'visible' => 'viewDocumentMenu',
                ],
                [
                    'label' => 'РЭМД',
                    'url' => ['remd/index'],
                    'icon' => 'clipboard',
                    'active' => $this->context->getUniqueId() == 'remd',
                    'visible' => 'viewRemdMenu',
                ],
                [
                    'label' => 'Сертификаты',
                    'url' => ['certificate/index'],
                    'icon' => 'pocket',
                    'active' => $this->context->getUniqueId() == 'certificate',
                    'visible' => 'viewCertificateMenu',
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
                    'visible' => ['viewUserMenu', 'viewRoleMenu'],
                ],
                [
                    'header' => 'Справочники',
                    'visible' => ['viewPositionMenu', 'viewEmployeeMenu', 'viewIssuerMenu'],
                ],
                [
                    'label' => 'Должности',
                    'url' => ['position/index'],
                    'icon' => 'grid',
                    'active' => $this->context->getUniqueId() == 'position',
                    'visible' => 'viewPositionMenu',
                ],
                [
                    'label' => 'Сотрудники',
                    'url' => ['employee/index'],
                    'icon' => 'user',
                    'active' => $this->context->getUniqueId() == 'employee',
                    'visible' => 'viewEmployeeMenu',
                ],
                                [
                    'label' => 'Удостоверяющие центры',
                    'url' => ['issuer/index'],
                    'icon' => 'check-square',
                    'active' => $this->context->getUniqueId() == 'issuer',
                    'visible' => 'viewIssuerMenu',
                ],
                [
                    'header' => 'Администрирование',
                    'visible' => ['viewUserMenu', 'viewRoleMenu'],
                ],
                [
                    'label' => 'Пользователи',
                    'url' => ['user/index'],
                    'icon' => 'users',
                    'active' => $this->context->getUniqueId() == 'user',
                    'visible' => 'viewUserMenu',
                ],
                [
                    'label' => 'Роли',
                    'url' => ['auth-item/index'],
                    'icon' => 'lock',
                    'active' => $this->context->getUniqueId() == 'auth-item',
                    'visible' => 'viewRoleMenu',
                ]
            ]
        ]) ?>

    </div>
</nav>