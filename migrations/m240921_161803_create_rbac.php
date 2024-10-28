<?php

use app\rbac\AuthorRule;
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
        $rule = new AuthorRule;
        $auth->add($rule);

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

        $viewPositionMenu = $auth->createPermission('viewPositionMenu');
        $viewPositionMenu->description = 'Отображать в меню';
        $auth->add($viewPositionMenu);

        $viewPositionList = $auth->createPermission('viewPositionList');
        $viewPositionList->description = 'Просматривать список';
        $auth->add($viewPositionList);

        $viewPosition = $auth->createPermission('viewPosition');
        $viewPosition->description = 'Просматривать';
        $auth->add($viewPosition);

        $createPosition = $auth->createPermission('createPosition');
        $createPosition->description = 'Добавлять';
        $auth->add($createPosition);

        $updatePosition = $auth->createPermission('updatePosition');
        $updatePosition->description = 'Редактировать';
        $auth->add($updatePosition);

        $viewEmployeeMenu = $auth->createPermission('viewEmployeeMenu');
        $viewEmployeeMenu->description = 'Отображать в меню';
        $auth->add($viewEmployeeMenu);

        $viewEmployeeList = $auth->createPermission('viewEmployeeList');
        $viewEmployeeList->description = 'Просматривать список';
        $auth->add($viewEmployeeList);

        $viewEmployee = $auth->createPermission('viewEmployee');
        $viewEmployee->description = 'Просматривать';
        $auth->add($viewEmployee);

        $createEmployee = $auth->createPermission('createEmployee');
        $createEmployee->description = 'Добавлять';
        $auth->add($createEmployee);

        $updateEmployee = $auth->createPermission('updateEmployee');
        $updateEmployee->description = 'Редактировать';
        $auth->add($updateEmployee);

        $viewDocumentMenu = $auth->createPermission('viewDocumentMenu');
        $viewDocumentMenu->description = 'Отображать в меню';
        $auth->add($viewDocumentMenu);

        $viewDocumentList = $auth->createPermission('viewDocumentList');
        $viewDocumentList->description = 'Просматривать список';
        $auth->add($viewDocumentList);

        $viewDocument = $auth->createPermission('viewDocument');
        $viewDocument->description = 'Просматривать';
        $auth->add($viewDocument);

        $createDocument = $auth->createPermission('createDocument');
        $createDocument->description = 'Добавлять';
        $auth->add($createDocument);

        $updateOwnDocument = $auth->createPermission('updateOwnDocument');
        $updateOwnDocument->description = 'Редактировать свои документы';
        $updateOwnDocument->ruleName = $rule->name;
        $auth->add($updateOwnDocument);

        $updateDocument = $auth->createPermission('updateDocument');
        $updateDocument->description = 'Редактировать';
        $auth->add($updateDocument);

        $auth->addChild($updateOwnDocument, $updateDocument);

        $publishOwnDocument = $auth->createPermission('publishOwnDocument');
        $publishOwnDocument->description = 'Публиковать свои документы';
        $publishOwnDocument->ruleName = $rule->name;
        $auth->add($publishOwnDocument);

        $publishDocument = $auth->createPermission('publishDocument');
        $publishDocument->description = 'Публиковать';
        $auth->add($publishDocument);

        $auth->addChild($publishOwnDocument, $publishDocument);

        $cancelOwnDocument = $auth->createPermission('cancelOwnDocument');
        $cancelOwnDocument->description = 'Отменять свои документы';
        $cancelOwnDocument->ruleName = $rule->name;
        $auth->add($cancelOwnDocument);

        $cancelDocument = $auth->createPermission('cancelDocument');
        $cancelDocument->description = 'Отменять';
        $auth->add($cancelDocument);

        $auth->addChild($cancelOwnDocument, $cancelDocument);

        $fileDownloadDocument = $auth->createPermission('fileDownloadDocument');
        $fileDownloadDocument->description = 'Скачивать файлы';
        $auth->add($fileDownloadDocument);

        $fileUploadOwnDocument = $auth->createPermission('fileUploadOwnDocument');
        $fileUploadOwnDocument->description = 'Загружать файлы в свои документы';
        $fileUploadOwnDocument->ruleName = $rule->name;
        $auth->add($fileUploadOwnDocument);

        $fileUploadDocument = $auth->createPermission('fileUploadDocument');
        $fileUploadDocument->description = 'Загружать файлы в документы';
        $auth->add($fileUploadDocument);

        $auth->addChild($fileUploadOwnDocument, $fileUploadDocument);

        $fileDeleteOwnDocument = $auth->createPermission('fileDeleteOwnDocument');
        $fileDeleteOwnDocument->description = 'Удалять свои файлы в документах';
        $fileDeleteOwnDocument->ruleName = $rule->name;
        $auth->add($fileDeleteOwnDocument);

        $fileDeleteDocument = $auth->createPermission('fileDeleteDocument');
        $fileDeleteDocument->description = 'Удалять файлы в документах';
        $auth->add($fileDeleteDocument);

        $auth->addChild($fileDeleteOwnDocument, $fileDeleteDocument);

        $accessOwnDocumentList = $auth->createPermission('accessOwnDocumentList');
        $accessOwnDocumentList->description = 'Отображать в своих документах';
        $accessOwnDocumentList->ruleName = $rule->name;
        $auth->add($accessOwnDocumentList);

        $accessDocumentList = $auth->createPermission('accessDocumentList');
        $accessDocumentList->description = 'Отображать в документах';
        $auth->add($accessDocumentList);

        $auth->addChild($accessOwnDocumentList, $accessDocumentList);

        $accessOwnDocumentAdd = $auth->createPermission('accessOwnDocumentAdd');
        $accessOwnDocumentAdd->description = 'Предоставлять доступ к своим документам';
        $accessOwnDocumentAdd->ruleName = $rule->name;
        $auth->add($accessOwnDocumentAdd);

        $accessDocumentAdd = $auth->createPermission('accessDocumentAdd');
        $accessDocumentAdd->description = 'Предоставлять доступ к документам';
        $auth->add($accessDocumentAdd);

        $auth->addChild($accessOwnDocumentAdd, $accessDocumentAdd);

        $accessOwnDocumentCancel = $auth->createPermission('accessOwnDocumentCancel');
        $accessOwnDocumentCancel->description = 'Отзывать свои права доступа к документам';
        $accessOwnDocumentCancel->ruleName = $rule->name;
        $auth->add($accessOwnDocumentCancel);

        $accessDocumentCancel = $auth->createPermission('accessDocumentCancel');
        $accessDocumentCancel->description = 'Отзывать права доступа к документам';
        $auth->add($accessDocumentCancel);

        $auth->addChild($accessOwnDocumentCancel, $accessDocumentCancel);

        $eventOwnDocumentView = $auth->createPermission('eventOwnDocumentView');
        $eventOwnDocumentView->description = 'Отображать в своих документах';
        $eventOwnDocumentView->ruleName = $rule->name;
        $auth->add($eventOwnDocumentView);

        $eventDocumentView = $auth->createPermission('eventDocumentView');
        $eventDocumentView->description = 'Отображать в документах';
        $auth->add($eventDocumentView);

        $auth->addChild($eventOwnDocumentView, $eventDocumentView);

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

        $auth->addChild($adminRole, $viewPositionMenu);
        $auth->addChild($adminRole, $viewPositionList);
        $auth->addChild($adminRole, $viewPosition);
        $auth->addChild($adminRole, $createPosition);
        $auth->addChild($adminRole, $updatePosition);

        $auth->addChild($adminRole, $viewEmployeeMenu);
        $auth->addChild($adminRole, $viewEmployeeList);
        $auth->addChild($adminRole, $viewEmployee);
        $auth->addChild($adminRole, $createEmployee);
        $auth->addChild($adminRole, $updateEmployee);

        $auth->addChild($adminRole, $viewDocumentMenu);
        $auth->addChild($adminRole, $viewDocumentList);
        $auth->addChild($adminRole, $viewDocument);
        $auth->addChild($adminRole, $createDocument);
        $auth->addChild($adminRole, $updateDocument);
        $auth->addChild($adminRole, $publishDocument);
        $auth->addChild($adminRole, $cancelDocument);

        $auth->addChild($adminRole, $fileDownloadDocument);
        $auth->addChild($adminRole, $fileUploadDocument);
        $auth->addChild($adminRole, $fileDeleteDocument);

        $auth->addChild($adminRole, $accessDocumentList);
        $auth->addChild($adminRole, $accessDocumentAdd);
        $auth->addChild($adminRole, $accessDocumentCancel);

        $auth->addChild($adminRole, $eventDocumentView);

        $auth->addChild($userRole, $viewDocumentMenu);
        $auth->addChild($userRole, $viewDocumentList);
        $auth->addChild($userRole, $viewDocument);
        $auth->addChild($userRole, $createDocument);
        $auth->addChild($userRole, $updateOwnDocument);
        $auth->addChild($userRole, $publishOwnDocument);
        $auth->addChild($userRole, $cancelOwnDocument);

        $auth->addChild($userRole, $fileDownloadDocument);
        $auth->addChild($userRole, $fileUploadOwnDocument);
        $auth->addChild($userRole, $fileDeleteOwnDocument);

        $auth->addChild($userRole, $accessOwnDocumentList);
        $auth->addChild($userRole, $accessOwnDocumentAdd);
        $auth->addChild($userRole, $accessOwnDocumentCancel);

        $auth->addChild($userRole, $eventOwnDocumentView);

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
