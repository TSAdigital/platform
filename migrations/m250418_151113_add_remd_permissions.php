<?php

use yii\db\Migration;

/**
 * Class m250418_151113_add_remd_permissions
 */
class m250418_151113_add_remd_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $viewRemdMenu = $auth->createPermission('viewRemdMenu');
        $viewRemdMenu->description = 'Отображать в меню';
        $auth->add($viewRemdMenu);

        $viewRemdList = $auth->createPermission('viewRemdList');
        $viewRemdList->description = 'Просматривать список';
        $auth->add($viewRemdList);

        $makeRemdSetting = $auth->createPermission('makeRemdSetting');
        $makeRemdSetting->description = 'Производить настройки';
        $auth->add($makeRemdSetting);

        $adminRole = $auth->getRole('administrator');

        $auth->addChild($adminRole, $viewRemdMenu);
        $auth->addChild($adminRole, $viewRemdList);
        $auth->addChild($adminRole, $makeRemdSetting);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $adminRole = $auth->getRole('administrator');

        $auth->removeChild($adminRole, $auth->getPermission('viewRemdMenu'));
        $auth->removeChild($adminRole, $auth->getPermission('viewRemdList'));
        $auth->removeChild($adminRole, $auth->getPermission('makeRemdSetting'));

        $auth->remove($auth->getPermission('viewRemdMenu'));
        $auth->remove($auth->getPermission('viewRemdList'));
        $auth->remove($auth->getPermission('makeRemdSetting'));
    }
}
