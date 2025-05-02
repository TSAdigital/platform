<?php

use yii\db\Migration;

/**
 * Class m250501_192819_optimize_remd_indexes
 */
class m250501_192819_optimize_remd_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-remd-type',
            '{{%remd}}',
            'type'
        );

        $this->createIndex(
            'idx-remd-type-registration_date',
            '{{%remd}}',
            ['type', 'registration_date']
        );

        $this->createIndex(
            'idx-remd_employee-remd_id-employee_id',
            '{{%remd_employee}}',
            ['remd_id', 'employee_id']
        );

        $this->createIndex(
            'idx-remd_employee-employee_id-remd_id',
            '{{%remd_employee}}',
            ['employee_id', 'remd_id']
        );

        $this->createIndex(
            'idx-employee-position_id-status',
            '{{%employee}}',
            ['position_id', 'status']
        );

        $this->createIndex(
            'idx-employee-last_name-first_name-middle_name',
            '{{%employee}}',
            ['last_name', 'first_name', 'middle_name']
        );

        $this->createIndex(
            'idx-employee-status',
            '{{%employee}}',
            'status'
        );

        $this->createIndex(
            'idx-employee-user_id-status',
            '{{%employee}}',
            ['user_id', 'status']
        );

        $this->createIndex(
            'idx-remd-registration_date-id',
            '{{%remd}}',
            ['registration_date', 'id']
        );

        $this->createIndex(
            'idx-remd-registration_date-type-id',
            '{{%remd}}',
            ['registration_date', 'type', 'id']
        );

        $this->createIndex(
            'idx-remd_employee-remd_id-employee_id-created_at',
            '{{%remd_employee}}',
            ['remd_id', 'employee_id', 'created_at']
        );

        $this->createIndex(
            'idx-remd_employee-remd_id-created_at',
            '{{%remd_employee}}',
            ['remd_id', 'created_at']
        );

        $this->createIndex(
            'idx-remd_employee-employee_id-created_at',
            '{{%remd_employee}}',
            ['employee_id', 'created_at']
        );

        $this->createIndex(
            'idx-employee-id-position_id-status',
            '{{%employee}}',
            ['id', 'position_id', 'status']
        );

        $this->createIndex(
            'idx-employee-last_name-first_name-middle_name-status',
            '{{%employee}}',
            ['last_name', 'first_name', 'middle_name', 'status']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-employee-user_id-status',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-status',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-last_name-first_name-middle_name',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-position_id-status',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-employee_id-remd_id',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-remd_id-employee_id',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd-type-registration_date',
            '{{%remd}}'
        );

        $this->dropIndex(
            'idx-remd-type',
            '{{%remd}}'
        );
        $this->dropIndex(
            'idx-employee-last_name-first_name-middle_name-status',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-employee-id-position_id-status',
            '{{%employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-employee_id-created_at',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-remd_id-employee_id-created_at',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd_employee-remd_id-created_at',
            '{{%remd_employee}}'
        );

        $this->dropIndex(
            'idx-remd-registration_date-type-id',
            '{{%remd}}'
        );

        $this->dropIndex(
            'idx-remd-registration_date-id',
            '{{%remd}}'
        );
    }
}
