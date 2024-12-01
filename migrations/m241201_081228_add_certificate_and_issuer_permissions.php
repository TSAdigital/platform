<?php

use yii\db\Migration;

/**
 * Class m241201_081228_add_certificate_and_issuer_permissions
 */
class m241201_081228_add_certificate_and_issuer_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $viewCertificateMenu = $auth->createPermission('viewCertificateMenu');
        $viewCertificateMenu->description = 'Отображать в меню';
        $auth->add($viewCertificateMenu);

        $viewCertificateList = $auth->createPermission('viewCertificateList');
        $viewCertificateList->description = 'Просматривать список';
        $auth->add($viewCertificateList);

        $viewCertificate = $auth->createPermission('viewCertificate');
        $viewCertificate->description = 'Просматривать';
        $auth->add($viewCertificate);

        $createCertificate = $auth->createPermission('createCertificate');
        $createCertificate->description = 'Добавлять';
        $auth->add($createCertificate);

        $updateCertificate = $auth->createPermission('updateCertificate');
        $updateCertificate->description = 'Редактировать';
        $auth->add($updateCertificate);

        $viewIssuerMenu = $auth->createPermission('viewIssuerMenu');
        $viewIssuerMenu->description = 'Отображать в меню';
        $auth->add($viewIssuerMenu);

        $viewIssuerList = $auth->createPermission('viewIssuerList');
        $viewIssuerList->description = 'Просматривать список';
        $auth->add($viewIssuerList);

        $viewIssuer = $auth->createPermission('viewIssuer');
        $viewIssuer->description = 'Просматривать';
        $auth->add($viewIssuer);

        $createIssuer = $auth->createPermission('createIssuer');
        $createIssuer->description = 'Добавлять';
        $auth->add($createIssuer);

        $updateIssuer = $auth->createPermission('updateIssuer');
        $updateIssuer->description = 'Редактировать';
        $auth->add($updateIssuer);

        $adminRole = $auth->getRole('administrator');

        $auth->addChild($adminRole, $viewCertificateMenu);
        $auth->addChild($adminRole, $viewCertificateList);
        $auth->addChild($adminRole, $viewCertificate);
        $auth->addChild($adminRole, $createCertificate);
        $auth->addChild($adminRole, $updateCertificate);

        $auth->addChild($adminRole, $viewIssuerMenu);
        $auth->addChild($adminRole, $viewIssuerList);
        $auth->addChild($adminRole, $viewIssuer);
        $auth->addChild($adminRole, $createIssuer);
        $auth->addChild($adminRole, $updateIssuer);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $adminRole = $auth->getRole('administrator');

        $auth->removeChild($adminRole, $auth->getPermission('viewCertificateMenu'));
        $auth->removeChild($adminRole, $auth->getPermission('viewCertificateList'));
        $auth->removeChild($adminRole, $auth->getPermission('viewCertificate'));
        $auth->removeChild($adminRole, $auth->getPermission('createCertificate'));
        $auth->removeChild($adminRole, $auth->getPermission('updateCertificate'));

        $auth->removeChild($adminRole, $auth->getPermission('viewIssuerMenu'));
        $auth->removeChild($adminRole, $auth->getPermission('viewIssuerList'));
        $auth->removeChild($adminRole, $auth->getPermission('viewIssuer'));
        $auth->removeChild($adminRole, $auth->getPermission('createIssuer'));
        $auth->removeChild($adminRole, $auth->getPermission('updateIssuer'));

        $auth->remove($auth->getPermission('viewCertificateMenu'));
        $auth->remove($auth->getPermission('viewCertificateList'));
        $auth->remove($auth->getPermission('viewCertificate'));
        $auth->remove($auth->getPermission('createCertificate'));
        $auth->remove($auth->getPermission('updateCertificate'));

        $auth->remove($auth->getPermission('viewIssuerMenu'));
        $auth->remove($auth->getPermission('viewIssuerList'));
        $auth->remove($auth->getPermission('viewIssuer'));
        $auth->remove($auth->getPermission('createIssuer'));
        $auth->remove($auth->getPermission('updateIssuer'));
    }
}
