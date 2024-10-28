<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_file}}`.
 */
class m241003_131740_create_document_file_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('document_file', [
            'id' => $this->primaryKey(),
            'document_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'stored_name' => $this->string()->notNull(),
            'size' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull(),
            's3_storage' => $this->integer()->defaultValue(0),
            'local_storage' => $this->integer()->defaultValue(0),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            'idx-document_file-document_id',
            'document_file',
            'document_id'
        );

        $this->createIndex(
            'idx-document_file-updated_by',
            'document_file',
            'updated_by'
        );

        $this->createIndex(
            'idx-document_file-created_by',
            'document_file',
            'created_by'
        );

        $this->addForeignKey(
            'fk-document_file-document_id',
            'document_file',
            'document_id',
            'document',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-document_file-created_by',
            'document_file',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-document_file-updated_by',
            'document_file',
            'updated_by',
            'user',
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
            'fk-document_file-document_id',
            'document_file'
        );

        $this->dropForeignKey(
            'fk-document_file-updated_by',
            'document_file'
        );

        $this->dropForeignKey(
            'fk-document_file-created_by',
            'document_file'
        );

        $this->dropIndex(
            'idx-document_file-document_id',
            'document_file'
        );

        $this->dropIndex(
            'idx-document_file-updated_by',
            'document_file'
        );

        $this->dropIndex(
            'idx-document_file-created_by',
            'document_file'
        );

        $this->dropTable('{{%document_file}}');
    }
}
