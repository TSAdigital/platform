<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_read}}`.
 */
class m241020_160642_create_document_read_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_read}}', [
            'id' => $this->primaryKey(),
            'document_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            '{{%idx-document-read-document_id}}',
            '{{%document_read}}',
            'document_id'
        );

        $this->createIndex(
            '{{%idx-document-read-user_id}}',
            '{{%document_read}}',
            'user_id'
        );

        $this->addForeignKey(
            '{{%fk-document-read-document_id}}',
            '{{%document_read}}',
            'document_id',
            '{{%document}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            '{{%fk-document-read-user_id}}',
            '{{%document_read}}',
            'user_id',
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
            '{{%fk-document-read-user_id}}',
            '{{%document_read}}'
        );

        $this->dropForeignKey(
            '{{%fk-document-read-document_id}}',
            '{{%document_read}}'
        );

        $this->dropIndex(
            '{{%idx-document-read-user_id}}',
            '{{%document_read}}'
        );

        $this->dropIndex(
            '{{%idx-document-read-document_id}}',
            '{{%document_read}}'
        );

        $this->dropTable('{{%document_read}}');
    }
}
