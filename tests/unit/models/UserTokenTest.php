<?php

namespace app\tests\unit\models;

use app\interfaces\TokenInterface;
use UnitTester;
use app\models\UserToken;
use \Codeception\Test\Unit;
use yii\base\Exception;
use yii\web\BadRequestHttpException;

class UserTokenTest extends Unit
{
    public const USER_1_TOKEN_LOGIN = '_cqiYCPDQdMZWZy2b1b5i-K-4Gv6l1v2';
    public const USER_1_TOKEN_LOGIN_EXPIRED = 'XtB7VnkLlhJpHPwmbvCqsFpLXQYwVC2r';
    public const USER_1_TOKEN_CHANGE_PASSWORD = 'Sf4G3uLzK1wvURQQvecXvGDXLyluiZoG';
    public const USER_2_TOKEN_LOGIN = '_S859nLgqZEFDTUI1AFxQrb4kV9eVs0w';
    public const USER_1_TOKEN_CHANGE_PASSWORD_EXPIRED = 'o6xlVq5HM5KdUtHJtML3q8VELJMvW7QS';
    public const INCORRECT_TOKEN_TYPE = -1;

    /**
     * @return void
     * @throws BadRequestHttpException
     */
    public function testCreate(): void
    {
        UnitTester::expectExMessage(function () {
            UserToken::create(UserTest::INCORRECT_USER_ID, TokenInterface::TYPE_LOGIN);
        }, 'User not found');

        UnitTester::expectExMessage(function () {
            UserToken::create(UserTest::USER_1_ID, self::INCORRECT_TOKEN_TYPE);
        }, 'Type is incorrect');

        expect_that(UserToken::create(UserTest::USER_1_ID, TokenInterface::TYPE_LOGIN));
    }

    /**
     * @return void
     */
    public function testFindUserIdByToken(): void
    {
        $userId = UserToken::findUserIdByLoginToken('');
        expect_that($userId === null);

        $userId = UserToken::findUserIdByLoginToken('a');
        expect_that($userId === null);

        $userId = UserToken::findUserIdByLoginToken(self::USER_1_TOKEN_LOGIN_EXPIRED);
        expect_that($userId === null);

        $userId = UserToken::findUserIdByLoginToken(self::USER_1_TOKEN_LOGIN);
        expect_that($userId === 1);
    }

    /**
     * @return void
     */
    public function testFindUserIdByPasswordToken(): void
    {
        $userId = UserToken::findUserIdByPasswordToken('');
        expect_that($userId === null);

        $userId = UserToken::findUserIdByPasswordToken('a');
        expect_that($userId === null);

        $userId = UserToken::findUserIdByPasswordToken(self::USER_1_TOKEN_CHANGE_PASSWORD);
        expect_that($userId === 1);

        $userId = UserToken::findUserIdByPasswordToken(self::USER_1_TOKEN_CHANGE_PASSWORD_EXPIRED);
        expect_that($userId === null);
    }

    /**
     * @return void
     */
    public function testDeleteToken(): void
    {
        UnitTester::expectExMessage(function () {
            UserToken::deleteToken('');
        }, 'Token is empty');

        try {
            UserToken::deleteToken(self::USER_1_TOKEN_CHANGE_PASSWORD);
            expect_that(true);

            UserToken::deleteToken('1234');
            expect_that(true);
        } catch (\Throwable $e) {
            expect_that(false);
        }
    }


    /**
     * @return void
     * @throws Exception
     */
    public function testGetTokenFromBearerHeader(): void
    {
        $token = UserToken::getTokenFromBearerHeader();
        expect_that(empty($token));
    }

    /**
     * @return void
     */
    public function testDeleteOldTokens(): void
    {
        expect_that(UserToken::deleteOldTokens());
    }
}
