<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%remd_plan}}`.
 */
class m250421_170245_create_remd_plan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%remd_plan}}', [
            'id' => $this->primaryKey(),
            'year' => $this->integer()->notNull(),
            'type' => $this->string()->defaultValue(null),
            'jan' => $this->integer()->defaultValue(null),
            'feb' => $this->integer()->defaultValue(null),
            'mar' => $this->integer()->defaultValue(null),
            'apr' => $this->integer()->defaultValue(null),
            'may' => $this->integer()->defaultValue(null),
            'jun' => $this->integer()->defaultValue(null),
            'jul' => $this->integer()->defaultValue(null),
            'aug' => $this->integer()->defaultValue(null),
            'sep' => $this->integer()->defaultValue(null),
            'oct' => $this->integer()->defaultValue(null),
            'nov' => $this->integer()->defaultValue(null),
            'dec' => $this->integer()->defaultValue(null),
            'q1' => $this->integer()->defaultValue(null),
            'q2' => $this->integer()->defaultValue(null),
            'q3' => $this->integer()->defaultValue(null),
            'q4' => $this->integer()->defaultValue(null),
            'year_plan' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%remd_plan}}');
    }
}
