<?php

use yii\db\Migration;

/**
 * Class m240921_161803_create_rbac
 */
class m240921_161803_create_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $viewUserMenu = $auth->createPermission('viewUserMenu');
        $viewUserMenu->description = 'Отображать в меню';
        $auth->add($viewUserMenu);

        $viewUserList = $auth->createPermission('viewUserList');
        $viewUserList->description = 'Просматривать список';
        $auth->add($viewUserList);

        $viewUser = $auth->createPermission('viewUser');
        $viewUser->description = 'Просматривать';
        $auth->add($viewUser);

        $createUser = $auth->createPermission('createUser');
        $createUser->description = 'Добавлять';
        $auth->add($createUser);

        $updateUser = $auth->createPermission('updateUser');
        $updateUser->description = 'Редактировать';
        $auth->add($updateUser);

        $blockUser = $auth->createPermission('blockUser');
        $blockUser->description = 'Блокировать';
        $auth->add($blockUser);

        $unlockUser = $auth->createPermission('unlockUser');
        $unlockUser->description = 'Разблокировать';
        $auth->add($unlockUser);

        $changePasswordUser = $auth->createPermission('changePasswordUser');
        $changePasswordUser->description = 'Изменять пароль';
        $auth->add($changePasswordUser);

        $viewRoleMenu = $auth->createPermission('viewRoleMenu');
        $viewRoleMenu->description = 'Отображать в меню';
        $auth->add($viewRoleMenu);

        $viewRoleList = $auth->createPermission('viewRoleList');
        $viewRoleList->description = 'Просматривать список';
        $auth->add($viewRoleList);

        $viewRole = $auth->createPermission('viewRole');
        $viewRole->description = 'Просматривать';
        $auth->add($viewRole);

        $createRole = $auth->createPermission('createRole');
        $createRole->description = 'Добавлять';
        $auth->add($createRole);

        $updateRole = $auth->createPermission('updateRole');
        $updateRole->description = 'Редактировать';
        $auth->add($updateRole);

        $blockRole = $auth->createPermission('blockRole');
        $blockRole->description = 'Блокировать';
        $auth->add($blockRole);

        $unlockRole = $auth->createPermission('unlockRole');
        $unlockRole->description = 'Разблокировать';
        $auth->add($unlockRole);

        $permissionUpdateRole = $auth->createPermission('permissionUpdateRole');
        $permissionUpdateRole->description = 'Изменять права доступа';
        $auth->add($permissionUpdateRole);

        $userRole = $auth->createRole('user');
        $userRole->description = 'Пользователь';
        $auth->add($userRole);

        $adminRole = $auth->createRole('administrator');
        $adminRole->description = 'Администратор';
        $auth->add($adminRole);

        $auth->addChild($adminRole, $viewUserMenu);
        $auth->addChild($adminRole, $viewUserList);
        $auth->addChild($adminRole, $viewUser);
        $auth->addChild($adminRole, $createUser);
        $auth->addChild($adminRole, $updateUser);
        $auth->addChild($adminRole, $blockUser);
        $auth->addChild($adminRole, $unlockUser);
        $auth->addChild($adminRole, $changePasswordUser);

        $auth->addChild($adminRole, $viewRoleMenu);
        $auth->addChild($adminRole, $viewRoleList);
        $auth->addChild($adminRole, $viewRole);
        $auth->addChild($adminRole, $createRole);
        $auth->addChild($adminRole, $updateRole);
        $auth->addChild($adminRole, $blockRole);
        $auth->addChild($adminRole, $unlockRole);
        $auth->addChild($adminRole, $permissionUpdateRole);

        $auth->assign($adminRole, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();
    }
}
