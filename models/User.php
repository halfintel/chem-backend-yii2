<?php

namespace app\models;

use app\interfaces\MailerInterface;
use app\interfaces\TokenInterface;
use app\interfaces\UserInterface;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\di\NotInstantiableException;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;
use yii\di\Container;
use yii\web\ServerErrorHttpException;

/**
 * @property int $id
 * @property string $email
 * @property string $password_hash
 * @property string $access_token
 */
class User extends ActiveRecord implements IdentityInterface, UserInterface
{
    public string $password;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'users';
    }

    /**
     * @return array{id: string}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'Id',
            'email' => 'Email',
            'password' => 'Password',
            'password_hash' => 'Password hash',
            'access_token' => 'Token',
            'is_deleted' => 'Is deleted?',
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['email', 'password'], 'required', 'message' => 'Email/password not found'],
            [['email'], 'string', 'message' => 'Email/password must be a string'],
            [['email'], 'trim'],

            [['email'], 'email', 'message' => 'Email is not correct'],
            [['email'], 'unique', 'message' => 'Email already exists'],
            [['password'], 'string', 'min' => 8, 'max' => 16],
            [['password'], 'match', 'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/', 'message' => 'Password must contain at least 1 uppercase letter 1 lowercase letter and 1 digit'],
        ];
    }


    /**
     * @param string $email
     * @param string $password
     * @return bool
     * @throws BadRequestHttpException
     */
    public static function create(string $email, string $password): bool
    {
        $user = new self();
        $user->email = $email;
        $user->password = $password;

        if (!$user->validate()) {
            $errors = $user->getFirstErrors();
            $errors = array_shift($errors);
            throw new BadRequestHttpException($errors);
        }
        $user->save();
        return true;
    }


    /**
     * @param string $email
     * @param string $password
     * @return string
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public static function login(string $email, string $password): string
    {
        if (empty($email) || empty($password)) {
            throw new BadRequestHttpException('Email/password not found');
        }
        $user = self::findOne(['email' => $email, 'is_deleted' => false]);
        if ($user === null) {
            throw new BadRequestHttpException('Incorrect email/password');
        }
        $user->password = $password;
        if (!$user->validatePassword()) {
            throw new BadRequestHttpException('Incorrect email/password');
        }
        $userToken = self::getUserToken();
        return $userToken::create($user->id, $userToken::TYPE_LOGIN);
    }


    /**
     * @param string $email
     * @return void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public static function forgotPasswordSendEmail(string $email): void
    {
        $user = self::findOneByEmail($email);
        if ($user === null) {
            throw new BadRequestHttpException('User not found');
        }

        $userToken = self::getUserToken();
        $token = $userToken::create($user->id, $userToken::TYPE_CHANGE_PASSWORD);
        $changePasswordUrl = Yii::$app->params['frontUrl'] . 'change-password/' . $token;

        $mailer = self::getMailer();
        $mailer->setEmail($email)
            ->setSubject('Change password')
            ->setBody('Your link to change password - ' . $changePasswordUrl)
            ->send();
    }


    /**
     * @param string $token
     * @param string $password
     * @return void
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public static function changePassword(string $token, string $password): void
    {
        $userToken = self::getUserToken();
        $userId = $userToken::findUserIdByPasswordToken($token);
        if ($userId === null) {
            throw new BadRequestHttpException('User not found');
        }
        $user = self::findIdentity($userId);
        if ($user === null) {
            throw new BadRequestHttpException('User not found');
        }
        $user->password = $password;
        if (!$user->validate()) {
            $errors = $user->getFirstErrors();
            $errors = array_shift($errors);
            throw new BadRequestHttpException($errors);
        }
        $user->save();
    }


    /**
     * @param string $email
     * @return User|null
     */
    public static function findOneByEmail(string $email): User|null
    {
        return self::findOne(['email' => $email, 'is_deleted' => false]);
    }

    /**
     * @param $insert
     * @return bool
     * @throws Exception
     */
    public function beforeSave($insert): bool
    {
        if ($this->isNewRecord) {
            $this->setPasswordHash();
        }
        return parent::beforeSave($insert);
    }

    // IdentityInterface

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string|null
     * @throws NotSupportedException
     */
    public function getAuthKey(): string|null
    {
        throw new NotSupportedException('"getAuthKey" is not implemented.');
    }


    /**
     * @param $authKey
     * @return bool|null
     * @throws NotSupportedException
     */
    public function validateAuthKey($authKey): bool|null
    {
        throw new NotSupportedException('"validateAuthKey" is not implemented.');
    }


    /**
     * @param $id
     * @return User|null
     */
    public static function findIdentity($id): User|null
    {
        return self::findOne(['id' => $id, 'is_deleted' => false]);
    }


    /**
     * @param $token
     * @param null $type
     * @return User|null
     * @throws BadRequestHttpException
     * @throws NotInstantiableException
     * @throws InvalidConfigException|ServerErrorHttpException
     */
    public static function findIdentityByAccessToken($token, $type = null): User|null
    {
        if (!is_string($token)) {
            throw new BadRequestHttpException('Token is not a string');
        }
        $userToken = self::getUserToken();

        $userId = $userToken::findUserIdByLoginToken($token);
        return self::findOne(['id' => $userId, 'is_deleted' => false]);
    }



    // private methods

    /**
     * @return void
     * @throws Exception
     */
    private function setPasswordHash(): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
    }

    /**
     * @return bool
     */
    private function validatePassword(): bool
    {
        return Yii::$app->getSecurity()->validatePassword($this->password, $this->password_hash);
    }

    /**
     * @return TokenInterface
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    private static function getUserToken(): TokenInterface
    {
        $container = new Container();
        $userToken = $container->get('app\models\UserToken');
        if ($userToken instanceof TokenInterface) {
            return $userToken;
        } else {
            throw new ServerErrorHttpException('Incorrect container');
        }
    }

    /**
     * @return MailerInterface
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    private static function getMailer(): MailerInterface
    {
        $container = new Container();
        $mailer = $container->get('app\models\Mailer');
        if ($mailer instanceof MailerInterface) {
            return $mailer;
        } else {
            throw new ServerErrorHttpException('Incorrect container');
        }
    }
}