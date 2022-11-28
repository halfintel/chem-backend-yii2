<?php

namespace app\tests\unit\models;

use app\models\Mailer;
use Codeception\Exception\ModuleException;
use UnitTester;
use yii\mail\MessageInterface;
use Codeception\Test\Unit;
use yii\web\BadRequestHttpException;

class MailerTest extends Unit
{
    public UnitTester $tester;


    /**
     * @return void
     * @throws BadRequestHttpException
     * @throws ModuleException
     */
    public function testEmailSend(): void
    {
        $mailer = new Mailer();
        $email = 'a@a.a';
        $changePasswordUrl = 'aaa';
        $mailer->setEmail($email)
            ->setSubject('Change password')
            ->setBody('Your link to change password - ' . $changePasswordUrl)
            ->send();

        $this->tester->seeEmailIsSent();

        $emailMessage = $this->tester->grabLastSentEmail();
        expect_that($emailMessage instanceof MessageInterface);
        $getTo = $emailMessage->getTo();
        expect_that(array_key_exists($email, $getTo));
        verify($emailMessage->getSubject())->equals('Change password');
        verify($emailMessage->toString())->stringContainsString('Your link to change password - ' . $changePasswordUrl);
    }

    /**
     * @return void
     */
    public function testEmailSendFail(): void
    {
        UnitTester::expectExMessage(function () {
            $mailer = new Mailer();
            $email = 'a';
            $changePasswordUrl = 'aaa';
            $mailer->setEmail($email)
                ->setSubject('Change password')
                ->setBody('Your link to change password - ' . $changePasswordUrl)
                ->send();
        }, 'Email is not correct');
    }
}