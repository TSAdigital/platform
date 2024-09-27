<?php

use yii\db\Migration;

/**
 * Class m240923_160201_add_status_to_auth_item
 */
class m240923_160201_add_status_to_auth_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('auth_item', 'status', $this->smallInteger()->defaultValue(1)->after('data'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('auth_item', 'status');
    }
}
