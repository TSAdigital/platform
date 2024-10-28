<?php

use yii\db\Migration;

/**
 * Class m240921_121803_add_admin_user
 */
class m240921_121803_add_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('user', [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('12345678'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'unique_id' => Yii::$app->security->generateRandomString(12),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('user', ['username' => 'admin']);
    }
}
