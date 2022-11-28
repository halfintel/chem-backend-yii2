<?php

namespace tests\api;

use ApiTester;

use app\tests\unit\models\UserTest;
use app\tests\unit\models\UserTokenTest;

/**
 * @skip user need change
 */
class UserCest
{

    /**
     * @param ApiTester $I
     * @return void
     */
    public function _before(ApiTester $I): void
    {
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function registrationWithoutEmailTest(ApiTester $I): void
    {
        $I->sendPost('/v1/registration', ['password' => UserTest::CORRECT_PASSWORD]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Email/password not found"}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function registrationSuccessTest(ApiTester $I): void
    {
        $I->sendPost('/v1/registration', ['email' => UserTest::CORRECT_EMAIL, 'password' => UserTest::CORRECT_PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'message' => [
                'token' => 'string'
            ],
        ]);
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function registrationDuplicateEmailTest(ApiTester $I): void
    {
        $I->sendPost('/v1/registration', ['email' => UserTest::USER_1_EMAIL, 'password' => UserTest::CORRECT_PASSWORD]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Email already exists"}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function registrationDuplicateDeletedEmailTest(ApiTester $I): void
    {
        $I->sendPost('/v1/registration', ['email' => UserTest::USER_2_EMAIL, 'password' => UserTest::CORRECT_PASSWORD]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Email already exists"}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function loginWithoutEmailTest(ApiTester $I): void
    {
        $I->sendPost('/v1/login', ['password' => UserTest::CORRECT_PASSWORD]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Email/password not found"}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function loginSuccessTest(ApiTester $I): void
    {
        $I->sendPost('/v1/login', ['email' => UserTest::USER_1_EMAIL, 'password' => UserTest::USER_1_PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'message' => [
                'token' => 'string'
            ],
        ]);
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function loginDeletedTest(ApiTester $I): void
    {
        $I->sendPost('/v1/login', ['email' => UserTest::USER_2_EMAIL, 'password' => UserTest::USER_2_PASSWORD]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Incorrect email/password"}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function logoutSuccessTest(ApiTester $I): void
    {
        $I->amBearerAuthenticated(UserTokenTest::USER_1_TOKEN_LOGIN);
        $I->sendPost('/v1/logout');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"message":[]}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function logoutWithoutTokenTest(ApiTester $I): void
    {
        $I->sendPost('/v1/logout');
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Your request was made with invalid credentials."}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function logoutWithTokenDeletedTest(ApiTester $I): void
    {
        $I->amBearerAuthenticated(UserTokenTest::USER_2_TOKEN_LOGIN);
        $I->sendPost('/v1/logout');
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Your request was made with invalid credentials."}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function pingGuestTest(ApiTester $I): void
    {
        $I->sendGet('/v1/ping');
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Your request was made with invalid credentials."}');
    }


    /**
     * @param ApiTester $I
     * @return void
     */
    public function pingUserTest(ApiTester $I): void
    {
        $I->amBearerAuthenticated(UserTokenTest::USER_1_TOKEN_LOGIN);
        $I->sendGet('/v1/ping');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"message":[]}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function forgotPasswordTest(ApiTester $I): void
    {
        $I->sendPost('/v1/forgot-password', ['email' => UserTest::USER_1_EMAIL]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"message":[]}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function forgotPasswordTestWithoutEmail(ApiTester $I): void
    {
        $I->sendPost('/v1/forgot-password');
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Email not found"}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function changePasswordTest(ApiTester $I): void
    {
        $I->sendPost('/v1/change-password/' . UserTokenTest::USER_1_TOKEN_CHANGE_PASSWORD, ['password' => UserTest::CORRECT_PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":true,"message":[]}');
    }

    /**
     * @param ApiTester $I
     * @return void
     */
    public function changePasswordTestWithoutPassword(ApiTester $I): void
    {
        $I->sendPost('/v1/change-password/' . UserTokenTest::USER_1_TOKEN_CHANGE_PASSWORD);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContains('{"success":false,"message":"Password not found"}');
    }

}
