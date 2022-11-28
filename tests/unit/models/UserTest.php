<?php

namespace app\tests\unit\models;

use UnitTester;
use app\models\User;
use Codeception\Test\Unit;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\mail\MessageInterface;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class UserTest extends Unit
{
    public const INCORRECT_USER_ID = -1;
    public const INCORRECT_EMAIL = 'aaa';
    public const INCORRECT_PASSWORD = 'aaaaaaaa';
    public const SHORT_PASSWORD = 'aaa';
    // correct registration/incorrect login
    public const CORRECT_EMAIL = 'test@test.com';
    public const CORRECT_PASSWORD = 'aA1bcdefg';
    // duplicate registration/correct login
    public const USER_1_ID = 1;
    public const USER_1_EMAIL = 'duplicate@a.a';
    public const USER_1_PASSWORD = 'aA1bcdefg';
    // duplicate (deleted) registration/incorrect login
    public const USER_2_ID = 1;
    public const USER_2_EMAIL = 'duplicate_deleted@a.a';
    public const USER_2_PASSWORD = 'aA1bcdefg';


    public UnitTester $tester;

    /**
     * @return void
     */
    public function testValidEmailPassword(): void
    {
        $user = new User();
        $user->email = self::INCORRECT_EMAIL;
        $user->password = self::INCORRECT_PASSWORD;
        expect_not($user->validate());

        $user->email = self::USER_1_EMAIL;
        $user->password = self::CORRECT_PASSWORD;
        expect_not($user->validate());

        $user->email = self::USER_2_EMAIL;
        $user->password = self::CORRECT_PASSWORD;
        expect_not($user->validate());

        $user->email = self::CORRECT_EMAIL;
        $user->password = self::CORRECT_PASSWORD;
        expect_that($user->validate());
    }


    /**
     * @return void
     * @throws BadRequestHttpException
     */
    public function testCreateUser(): void
    {
        UnitTester::expectExMessage(function () {
            User::create(self::CORRECT_EMAIL, '');
        }, 'Email/password not found');

        UnitTester::expectExMessage(function () {
            User::create('', self::CORRECT_PASSWORD);
        }, 'Email/password not found');

        UnitTester::expectExMessage(function () {
            User::create(self::INCORRECT_EMAIL, self::CORRECT_PASSWORD);
        }, 'Email is not correct');

        UnitTester::expectExMessage(function () {
            User::create(self::USER_1_EMAIL, self::CORRECT_PASSWORD);
        }, 'Email already exists');

        UnitTester::expectExMessage(function () {
            User::create(self::USER_2_EMAIL, self::CORRECT_PASSWORD);
        }, 'Email already exists');

        UnitTester::expectExMessage(function () {
            User::create(self::CORRECT_EMAIL, self::SHORT_PASSWORD);
        }, 'Password should contain at least 8 characters.');

        UnitTester::expectExMessage(function () {
            User::create(self::CORRECT_EMAIL, self::INCORRECT_PASSWORD);
        }, 'Password must contain at least 1 uppercase letter 1 lowercase letter and 1 digit');


        expect_that(User::create(self::CORRECT_EMAIL, self::CORRECT_PASSWORD));
    }


    /**
     * @return void
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function testLoginUser(): void
    {
        UnitTester::expectExMessage(function () {
            User::login(self::USER_1_EMAIL, '');
        }, 'Email/password not found');

        UnitTester::expectExMessage(function () {
            User::login('', self::USER_1_PASSWORD);
        }, 'Email/password not found');

        UnitTester::expectExMessage(function () {
            User::login(self::USER_1_EMAIL, self::INCORRECT_PASSWORD);
        }, 'Incorrect email/password');

        UnitTester::expectExMessage(function () {
            User::login(self::USER_2_EMAIL, self::USER_2_PASSWORD);
        }, 'Incorrect email/password');

        expect_that($token = User::login(self::USER_1_EMAIL, self::USER_1_PASSWORD));
        expect_that(!empty($token));
    }

    /**
     * @return void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public function testFindUserByAccessToken(): void
    {
        UnitTester::expectExMessage(function () {
            User::findIdentityByAccessToken(111);
        }, 'Token is not a string');
        expect_not(User::findIdentityByAccessToken('non-existing'));
        expect_not(User::findIdentityByAccessToken(UserTokenTest::USER_2_TOKEN_LOGIN));

        $user = User::findIdentityByAccessToken(UserTokenTest::USER_1_TOKEN_LOGIN);
        if ($user === null) {
            expect_that(false);
        } else {
            expect_that($user->email === self::USER_1_EMAIL);
        }
    }

    /**
     * @return void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public function testGetId(): void
    {
        $user = User::findIdentityByAccessToken(UserTokenTest::USER_1_TOKEN_LOGIN);
        if ($user === null) {
            expect_that(false);
        } else {
            expect_that($user->getId() === self::USER_1_ID);
        }
    }

    /**
     * @return void
     */
    public function testGetAuthKey(): void
    {
        UnitTester::expectExMessage(function () {
            $user = User::findIdentityByAccessToken(UserTokenTest::USER_1_TOKEN_LOGIN);
            if ($user === null) {
                expect_that(false);
            } else {
                expect_that($user->getAuthKey());
            }
        }, '"getAuthKey" is not implemented.');
    }

    /**
     * @return void
     */
    public function testValidateAuthKey(): void
    {
        UnitTester::expectExMessage(function () {
            $user = User::findIdentityByAccessToken(UserTokenTest::USER_1_TOKEN_LOGIN);
            if ($user === null) {
                expect_that(false);
            } else {
                expect_that($user->validateAuthKey(''));
            }
        }, '"validateAuthKey" is not implemented.');
    }

    /**
     * @return void
     */
    public function testFindIdentity(): void
    {
        $user = User::findIdentity('');
        expect_that($user === null);

        $user = User::findIdentity(1);
        if ($user === null) {
            expect_that(false);
        } else {
            expect_that($user->email === self::USER_1_EMAIL);
        }
    }

    /**
     * @return void
     */
    public function testForgotPasswordSendEmail(): void
    {
        UnitTester::expectExMessage(function () {
            User::forgotPasswordSendEmail('');
        }, 'User not found');

        try {
            User::forgotPasswordSendEmail(self::USER_1_EMAIL);
            $this->tester->seeEmailIsSent();

            $emailMessage = $this->tester->grabLastSentEmail();
            expect_that($emailMessage instanceof MessageInterface);
            $getTo = $emailMessage->getTo();
            expect_that(array_key_exists(self::USER_1_EMAIL, $getTo));
            verify($emailMessage->getSubject())->equals('Change password');
            verify($emailMessage->toString())->stringContainsString('Your link to change password - ');
            expect_that(true);
        } catch (\Throwable $e) {
            expect_that(false);
        }
    }


    /**
     * @return void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public function testChangePassword(): void
    {
        UnitTester::expectExMessage(function () {
            User::changePassword('', '');
        }, 'User not found');

        UnitTester::expectExMessage(function () {
            User::changePassword(UserTokenTest::USER_1_TOKEN_CHANGE_PASSWORD, '');
        }, 'Email/password not found');

        User::changePassword(UserTokenTest::USER_1_TOKEN_CHANGE_PASSWORD, self::CORRECT_PASSWORD);
    }

    /**
     * @return void
     */
    public function testFindOneByEmail(): void
    {
        $user = User::findOneByEmail('');
        expect_that($user === null);

        $user = User::findOneByEmail(self::USER_1_EMAIL);
        expect_that($user !== null);
    }
}
