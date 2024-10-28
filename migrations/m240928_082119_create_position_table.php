<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%position}}`.
 */
class m240928_082119_create_position_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%position}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'status' => $this->integer()->defaultValue(1)->notNull(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            'idx-position-created_by',
            '{{%position}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-position-created_by',
            '{{%position}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx-position-updated_by',
            '{{%position}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-position-updated_by',
            '{{%position}}',
            'updated_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-position-created_by',
            '{{%position}}'
        );

        $this->dropIndex(
            'idx-position-created_by',
            '{{%position}}'
        );

        $this->dropForeignKey(
            'fk-position-updated_by',
            '{{%position}}'
        );

        $this->dropIndex(
            'idx-position-updated_by',
            '{{%position}}'
        );

        $this->dropTable('{{%position}}');
    }
}
