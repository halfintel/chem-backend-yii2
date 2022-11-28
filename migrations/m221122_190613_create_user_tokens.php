<?php

use yii\db\Schema;
use yii\db\Migration;

class m221122_190613_create_user_tokens extends Migration
{
    private const TABLE = 'user_tokens';

    /**
     * @return void
     */
    public function safeUp(): void
    {
        $this->createTable(self::TABLE, [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'type' => Schema::TYPE_TINYINT . ' UNSIGNED NOT NULL',
            'token_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'created_at' => Schema::TYPE_DATETIME . ' NOT NULL DEFAULT now()',
        ]);
        $this->createIndex('token_hash_unique', self::TABLE, 'token_hash', true);
        $this->addForeignKey(
            'fk_user_tokens_user_id',
            self::TABLE,
            'user_id',
            'users',
            'id',
            'CASCADE'
        );
    }

    /**
     * @return void
     */
    public function safeDown(): void
    {
        $this->dropForeignKey(
            'fk_user_tokens_user_id',
            self::TABLE
        );
        $this->dropIndex('token_hash_unique', self::TABLE);
        $this->dropTable(self::TABLE);
    }
}