<?php

namespace app\models;

use app\interfaces\MailerInterface;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;


class Mailer extends Model implements MailerInterface
{
    private string $email;
    private string $subject;
    private string $body;


    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['email', 'subject', 'body'], 'required'],
            ['email', 'email', 'message' => 'Email is not correct'],
        ];
    }


    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): Mailer
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    protected function getEmail(): string
    {
        return $this->email;
    }


    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): Mailer
    {
        $this->subject = $subject;
        return $this;
    }


    /**
     * @return string
     */
    protected function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody(string $body): Mailer
    {
        $this->body = $body;
        return $this;
    }


    /**
     * @return string
     */
    protected function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return void
     * @throws BadRequestHttpException
     */
    public function send(): void
    {
        if ($this->validate()) {
            Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($this->getEmail())
                ->setSubject($this->getSubject())
                ->setTextBody($this->getBody())
                ->send();
        } else {
            $errors = $this->getFirstErrors();
            $errors = array_shift($errors);
            throw new BadRequestHttpException($errors);
        }
    }
}