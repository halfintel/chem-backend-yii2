<?php

use yii\db\Migration;
use app\interfaces\TokenInterface;

class m221122_193456_test_add_data_user_tokens extends Migration
{
    private const TABLE = 'user_tokens';

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
            'user_id' => 1,
            'type' => TokenInterface::TYPE_LOGIN,
            'token_hash' => 'BdT.syajr5JMs',// for token '_cqiYCPDQdMZWZy2b1b5i-K-4Gv6l1v2'
        ]);
        $this->insert(self::TABLE, [
            'id' => 2,
            'user_id' => 2,
            'type' => TokenInterface::TYPE_LOGIN,
            'token_hash' => 'BdqfM33ZaWivU',// for token '_S859nLgqZEFDTUI1AFxQrb4kV9eVs0w'
        ]);
        $this->insert(self::TABLE, [
            'id' => 3,
            'user_id' => 1,
            'type' => TokenInterface::TYPE_CHANGE_PASSWORD,
            'token_hash' => 'BdzNBgaV6JPfk',// for token 'Sf4G3uLzK1wvURQQvecXvGDXLyluiZoG'
        ]);
        $this->insert(self::TABLE, [
            'id' => 4,
            'user_id' => 1,
            'type' => TokenInterface::TYPE_CHANGE_PASSWORD,
            'token_hash' => 'Bd8jwgbuLfMNI',// for token 'o6xlVq5HM5KdUtHJtML3q8VELJMvW7QS'
            'created_at' => '2020-01-01 01:00:00'
        ]);
        $this->insert(self::TABLE, [
            'id' => 5,
            'user_id' => 1,
            'type' => TokenInterface::TYPE_LOGIN,
            'token_hash' => 'Bd7JSHsua1mA2',// for token 'XtB7VnkLlhJpHPwmbvCqsFpLXQYwVC2r'
            'created_at' => '2020-01-01 01:00:00'
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
        $this->delete(self::TABLE, ['id' => 3]);
        $this->delete(self::TABLE, ['id' => 4]);
        $this->delete(self::TABLE, ['id' => 5]);
    }
}