<?php

namespace app\interfaces;

interface UserInterface
{

    /**
     * @param string $email
     * @param string $password
     * @return bool
     */
    public static function create(string $email, string $password): bool;

    /**
     * @param string $email
     * @param string $password
     * @return string
     */
    public static function login(string $email, string $password): string;

    /**
     * @param string $email
     * @return void
     */
    public static function forgotPasswordSendEmail(string $email): void;

    /**
     * @param string $token
     * @param string $password
     * @return void
     */
    public static function changePassword(string $token, string $password): void;
}
