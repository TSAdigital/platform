<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_offsets}}`.
 */
class m241013_155710_create_telegram_offsets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%telegram_offsets}}', [
            'id' => $this->primaryKey(),
            'offset' => $this->integer()->notNull(),
        ]);

        $this->insert('telegram_offsets', ['offset' => 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%telegram_offsets}}');
    }
}
