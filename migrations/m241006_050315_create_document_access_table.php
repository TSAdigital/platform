<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_access}}`.
 */
class m241006_050315_create_document_access_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_access}}', [
            'id' => $this->primaryKey(),
            'document_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->addForeignKey(
            'fk_document_access_document_id',
            '{{%document_access}}',
            'document_id',
            '{{%document}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_document_access_user_id',
            '{{%document_access}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_document_access_created_by',
            '{{%document_access}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_document_access_updated_by',
            '{{%document_access}}',
            'updated_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx_document_id',
            '{{%document_access}}',
            'document_id'
        );

        $this->createIndex(
            'idx_user_id',
            '{{%document_access}}',
            'user_id'
        );

        $this->createIndex(
            'idx_created_by',
            '{{%document_access}}',
            'created_by'
        );

        $this->createIndex(
            'idx_updated_by',
            '{{%document_access}}',
            'updated_by'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_document_access_document_id',
            '{{%document_access}}'
        );

        $this->dropForeignKey(
            'fk_document_access_user_id',
            '{{%document_access}}'
        );

        $this->dropForeignKey(
            'fk_document_access_created_by',
            '{{%document_access}}'
        );

        $this->dropForeignKey(
            'fk_document_access_updated_by',
            '{{%document_access}}'
        );

        $this->dropIndex(
            'idx_document_id',
            '{{%document_access}}'
        );

        $this->dropIndex(
            'idx_user_id',
            '{{%document_access}}'
        );

        $this->dropIndex(
            'idx_created_by',
            '{{%document_access}}'
        );

        $this->dropIndex(
            'idx_updated_by',
            '{{%document_access}}'
        );

        $this->dropTable('{{%document_access}}');
    }
}
