<?php

use yii\db\Migration;

/**
 * Class m250403_133521_add_certificate_analytics_permissions
 */
class m250403_133521_add_certificate_analytics_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $viewCertificateAnalytics = $auth->createPermission('viewCertificateAnalytics');
        $viewCertificateAnalytics->description = 'Просматривать аналитику';
        $auth->add($viewCertificateAnalytics);

        $adminRole = $auth->getRole('administrator');

        $auth->addChild($adminRole, $viewCertificateAnalytics);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $adminRole = $auth->getRole('administrator');

        $auth->removeChild($adminRole, $auth->getPermission('viewCertificateAnalytics'));

        $auth->remove($auth->getPermission('viewCertificateAnalytics'));
    }
}
