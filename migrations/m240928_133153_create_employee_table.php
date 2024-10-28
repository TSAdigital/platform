<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee}}`.
 */
class m240928_133153_create_employee_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employee}}', [
            'id' => $this->primaryKey(),
            'first_name' => $this->string()->notNull(),
            'last_name' => $this->string()->notNull(),
            'middle_name' => $this->string()->null(),
            'birth_date' => $this->date()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'position_id' => $this->integer()->notNull(),
            'status' => $this->integer()->defaultValue(1)->notNull(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci');

        $this->createIndex(
            'idx-employee-user_id',
            '{{%employee}}',
            'user_id'
        );

        $this->createIndex(
            'idx-employee-position_id',
            '{{%employee}}',
            'position_id'
        );

        $this->addForeignKey(
            'fk-employee-user_id',
            '{{%employee}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-employee-position_id',
            '{{%employee}}',
            'position_id',
            '{{%position}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx-employee-created_by',
            '{{%employee}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-employee-created_by',
            '{{%employee}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'idx-employee-updated_by',
            '{{%employee}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-employee-updated_by',
            '{{%employee}}',
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
            'fk-employee-position_id',
            '{{%employee}}'
        );

        $this->dropForeignKey(
            'fk-employee-user_id',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-position_id',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-user_id',
            '{{%employee}}'
        );

        $this->dropForeignKey(
            'fk-employee-created_by',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-created_by',
            '{{%employee}}'
        );

        $this->dropForeignKey(
            'fk-employee-updated_by',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-updated_by',
            '{{%employee}}'
        );

        $this->dropTable('{{%employee}}');
    }
}
