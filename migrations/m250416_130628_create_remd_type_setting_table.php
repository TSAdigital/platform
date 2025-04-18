<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%remd_type_setting}}`.
 */
class m250416_130628_create_remd_type_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%remd_type_setting}}', [
            'id' => $this->primaryKey(),
            'enabled_doc_types' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%remd_type_setting}}');
    }
}
