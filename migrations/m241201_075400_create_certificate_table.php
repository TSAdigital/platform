<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%certificate}}`.
 */
class m241201_075400_create_certificate_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%certificate}}', [
            'id' => $this->primaryKey(),
            'employee_id' => $this->integer()->notNull(),
            'serial_number' => $this->string(255)->unique(),
            'valid_from' => $this->date()->notNull(),
            'valid_to' => $this->date()->notNull(),
            'issuer_id' => $this->integer()->notNull(),
            'status' => $this->integer()->defaultValue(1)->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            'idx_certificate_employee_id',
            '{{%certificate}}',
            'employee_id'
        );

        $this->createIndex(
            'idx_certificate_issuer_id',
            '{{%certificate}}',
            'issuer_id'
        );

        $this->createIndex(
            'idx_certificate_created_by',
            '{{%certificate}}',
            'created_by'
        );

        $this->createIndex(
            'idx_certificate_updated_by',
            '{{%certificate}}',
            'updated_by'
        );

        $this->addForeignKey(
            'fk_certificate_employee',
            '{{%certificate}}',
            'employee_id',
            '{{%employee}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_certificate_issuer',
            '{{%certificate}}',
            'issuer_id',
            '{{%issuer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_certificate_created_by',
            '{{%certificate}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_certificate_updated_by',
            '{{%certificate}}',
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
            'fk_certificate_updated_by',
            '{{%certificate}}'
        );

        $this->dropForeignKey(
            'fk_certificate_issuer',
            '{{%certificate}}'
        );

        $this->dropForeignKey(
            'fk_certificate_created_by',
            '{{%certificate}}'
        );

        $this->dropForeignKey(
            'fk_certificate_employee',
            '{{%certificate}}'
        );

        $this->dropIndex(
            'idx_certificate_issuer_id',
            '{{%certificate}}'
        );

        $this->dropIndex(
            'idx_certificate_updated_by',
            '{{%certificate}}'
        );

        $this->dropIndex(
            'idx_certificate_created_by',
            '{{%certificate}}'
        );

        $this->dropIndex(
            'idx_certificate_employee_id',
            '{{%certificate}}'
        );

        $this->dropTable('{{%certificate}}');
    }
}
