<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%remd_base_setting}}`.
 */
class m250418_121938_create_remd_base_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%remd_base_setting}}', [
            'id' => $this->primaryKey(),
            'date_from' => $this->date()->null(),
            'date_to' => $this->date()->null(),
            'date_of_update' => $this->date()->null(),
            'page_size' => $this->integer()->null(),
            'lk_document_filter_enabled' => $this->boolean()->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%remd_base_setting}}');
    }
}
