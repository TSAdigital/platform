<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%document_event}}`.
 */
class m241022_125559_create_document_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%document_event}}', [
            'id' => $this->primaryKey(),
            'document_id' => $this->integer()->notNull(),
            'event' => $this->text()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            'idx-document_event-document_id',
            '{{%document_event}}',
            'document_id'
        );

        $this->createIndex(
            'idx-document_event-user_id',
            '{{%document_event}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-document_event-document_id',
            '{{%document_event}}',
            'document_id',
            '{{%document}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-document_event-user_id',
            '{{%document_event}}',
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
            'fk-document_event-document_id',
            '{{%document_event}}'
        );

        $this->dropForeignKey(
            'fk-document_event-user_id',
            '{{%document_event}}'
        );

        $this->dropIndex(
            'idx-document_event-document_id',
            '{{%document_event}}'
        );

        $this->dropIndex(
            'idx-document_event-user_id',
            '{{%document_event}}'
        );

        $this->dropTable('{{%document_event}}');
    }
}
