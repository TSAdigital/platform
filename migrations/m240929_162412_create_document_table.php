<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document}}`.
 */
class m240929_162412_create_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'status' => $this->integer()->defaultValue(1)->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            'idx-document-created_by',
            '{{%document}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-document-created_by',
            '{{%document}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx-document-updated_by',
            '{{%document}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-document-updated_by',
            '{{%document}}',
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
        'fk-document-created_by',
        '{{%document}}'
        );

        $this->dropIndex(
        'idx-document-created_by',
        '{{%document}}'
        );

        $this->dropForeignKey(
        'fk-document-updated_by',
        '{{%document}}'
        );

        $this->dropIndex(
        'idx-document-updated_by',
        '{{%document}}'
        );

        $this->dropTable('{{%document}}');
    }
}
