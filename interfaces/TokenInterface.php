<?php

namespace app\interfaces;

interface TokenInterface
{
    public const TYPE_LOGIN = 1;
    public const TYPE_CHANGE_PASSWORD = 2;

    /**
     * @param int $userId
     * @param int $type
     * @return string
     */
    public static function create(int $userId, int $type): string;

    /**
     * @param string $token
     * @return int|null
     */
    public static function findUserIdByPasswordToken(string $token): int|null;

    /**
     * @param string $token
     * @return int|null
     */
    public static function findUserIdByLoginToken(string $token): int|null;

    /**
     * @return string
     */
    public static function getTokenFromBearerHeader(): string;

    /**
     * @param string $token
     * @return void
     */
    public static function deleteToken(string $token): void;
}
