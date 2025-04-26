<?php

use yii\db\Migration;

/**
 * Class m250426_095709_add_new_fields_to_remd_base_setting
 */
class m250426_095709_add_new_fields_to_remd_base_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%remd_base_setting}}', 'use_caching', $this->boolean()->null()->after('lk_document_filter_enabled'));
        $this->addColumn('{{%remd_base_setting}}', 'analytics_period', $this->integer()->null()->after('use_caching'));
        $this->addColumn('{{%remd_base_setting}}', 'hide_empty_months', $this->boolean()->null()->after('analytics_period'));
        $this->addColumn('{{%remd_base_setting}}', 'chart_type', $this->string()->null()->after('hide_empty_months'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%remd_base_setting}}', 'use_caching');
        $this->dropColumn('{{%remd_base_setting}}', 'analytics_period');
        $this->dropColumn('{{%remd_base_setting}}', 'hide_empty_months');
        $this->dropColumn('{{%remd_base_setting}}', 'chart_type');
    }
}
