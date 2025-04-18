<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%remd_employee}}`.
 */
class m250412_035621_create_remd_employee_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%remd_employee}}', [
            'remd_id' => $this->integer()->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer(),
        ]);

        $this->addPrimaryKey(
            'pk-remd_employee',
            '{{%remd_employee}}',
            ['remd_id', 'employee_id']
        );

        $this->createIndex(
            'idx-remd_employee-remd_id',
            '{{%remd_employee}}',
            'remd_id'
        );

        $this->createIndex(
            'idx-remd_employee-employee_id',
            '{{%remd_employee}}',
            'employee_id'
        );

        $this->createIndex(
            'idx-remd_employee-created_at',
            '{{%remd_employee}}',
            'created_at'
        );

        $this->addForeignKey(
            'fk_remd_employee_remd_id',
            '{{%remd_employee}}',
            'remd_id',
            '{{%remd}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_remd_employee_employee_id',
            '{{%remd_employee}}',
            'employee_id',
            '{{%employee}}',
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
            'fk_remd_employee_remd_id',
            '{{%remd_employee}}'
        );

        $this->dropForeignKey(
            'fk_remd_employee_employee_id',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-remd_id',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-employee_id',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-created_at',
            '{{%remd_employee}}'
        );

        $this->dropPrimaryKey(
            'pk-remd_employee',
            '{{%remd_employee}}'
        );

        $this->dropTable('{{%remd_employee}}');
    }
}
