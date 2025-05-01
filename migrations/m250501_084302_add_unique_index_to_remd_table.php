<?php

use yii\db\Migration;

/**
 * Class m250501_084302_add_unique_index_to_remd_table
 */
class m250501_084302_add_unique_index_to_remd_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx_remd_unique_code',
            'remd',
            'unique_code',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_remd_unique_code', 'remd');
    }
}
