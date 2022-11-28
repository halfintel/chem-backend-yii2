<?php

use yii\db\Schema;
use yii\db\Migration;

class m221122_191525_test_add_data_users extends Migration
{
    private const TABLE = 'users';

    /**
     * @return void
     */
    public function safeUp(): void
    {
        if (!defined('CONSOLE_TEST')) {
            return;
        }
        $this->insert(self::TABLE, [
            'id' => 1,
            'email' => 'duplicate@a.a',
            'password_hash' => '$2y$13$XWuI0hmLjMDJNTtu0vZHcOosQZEiXCjteI0gsspeoKHx/SgI87YuW',
            'is_deleted' => false,
        ]);
        $this->insert(self::TABLE, [
            'id' => 2,
            'email' => 'duplicate_deleted@a.a',
            'password_hash' => '$2y$13$XWuI0hmLjMDJNTtu0vZHcOosQZEiXCjteI0gsspeoKHx/SgI87YuW',
            'is_deleted' => true,
        ]);
    }

    /**
     * @return void
     */
    public function safeDown(): void
    {
        if (!defined('CONSOLE_TEST')) {
            return;
        }
        $this->delete(self::TABLE, ['id' => 1]);
        $this->delete(self::TABLE, ['id' => 2]);
    }
}