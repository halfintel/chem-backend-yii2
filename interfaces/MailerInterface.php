<?php

namespace app\interfaces;

interface MailerInterface
{
    /**
     * @param string $email
     * @return MailerInterface
     */
    public function setEmail(string $email): MailerInterface;

    /**
     * @param string $subject
     * @return MailerInterface
     */
    public function setSubject(string $subject): MailerInterface;

    /**
     * @param string $body
     * @return MailerInterface
     */
    public function setBody(string $body): MailerInterface;

    /**
     * @return void
     */
    public function send(): void;
}
