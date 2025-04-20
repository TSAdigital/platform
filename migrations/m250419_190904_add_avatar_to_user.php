<?php

use yii\db\Migration;

/**
 * Class m250419_190904_add_avatar_to_user
 */
class m250419_190904_add_avatar_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'avatar', $this->string(255)->null()->after('email'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'avatar');
    }
}
