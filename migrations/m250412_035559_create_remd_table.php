<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%remd}}`.
 */
class m250412_035559_create_remd_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%remd}}', [
            'id' => $this->primaryKey(),
            'unique_code' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'registration_date' => $this->date()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex(
            'idx-remd-registration_date',
            '{{%remd}}',
            'registration_date'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%remd}}');
    }
}
