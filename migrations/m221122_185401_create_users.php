<?php

use yii\db\Schema;
use yii\db\Migration;

class m221122_185401_create_users extends Migration
{
    private const TABLE = 'users';

    /**
     * @return void
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => Schema::TYPE_PK,
            'email' => Schema::TYPE_STRING . ' NOT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'is_deleted' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT false',
        ]);
    }

    /**
     * @return void
     */
    public function safeDown(): void
    {
        $this->dropTable(self::TABLE);
    }
}