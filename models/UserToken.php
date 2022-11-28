<?php

namespace app\models;

use app\interfaces\TokenInterface;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;

/**
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property string $token_hash
 * @property string $created_at
 */
class UserToken extends ActiveRecord implements TokenInterface
{
    private const LOGIN_INTERVAL = '(now() - interval 30 day)';
    private const CHANGE_PASSWORD_INTERVAL = '(now() - interval 5 minute)';
    public string $token;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'user_tokens';
    }


    /**
     * @return array{id: string}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'Id',
            'user_id' => 'User id',
            'type' => 'Type',
            'token_hash' => 'Access token hash',
            'created_at' => 'Date of create token',
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['user_id'], 'required', 'message' => 'User id not found'],
            ['user_id', 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id'], 'message' => 'User not found'],

            [['token_hash'], 'unique', 'message' => 'Token hash not unique'],
            ['type', 'in', 'range' => [TokenInterface::TYPE_LOGIN, TokenInterface::TYPE_CHANGE_PASSWORD], 'message' => 'Type is incorrect']
        ];
    }


    /**
     * @param $insert
     * @return bool
     * @throws Exception
     */
    public function beforeSave($insert): bool
    {
        if ($this->isNewRecord) {
            $this->setToken();
            $this->setTokenHash();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param int $userId
     * @param int $type
     * @return string
     * @throws BadRequestHttpException
     */
    public static function create(int $userId, int $type): string
    {
        $userToken = new self();
        $userToken->user_id = $userId;
        $userToken->type = $type;

        if (!$userToken->validate()) {
            $errors = $userToken->getFirstErrors();
            $errors = array_shift($errors);
            throw new BadRequestHttpException($errors);
        }
        $userToken->save();
        return $userToken->token;
    }

    /**
     * @param string $token
     * @return int|null
     */
    public static function findUserIdByLoginToken(string $token): int|null
    {
        return self::findUserIdByToken($token, self::TYPE_LOGIN, self::LOGIN_INTERVAL);
    }

    /**
     * @param string $token
     * @return int|null
     */
    public static function findUserIdByPasswordToken(string $token): int|null
    {
        return self::findUserIdByToken($token, self::TYPE_CHANGE_PASSWORD, self::CHANGE_PASSWORD_INTERVAL);
    }


    /**
     * @param string $token
     * @return void
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function deleteToken(string $token): void
    {
        if (empty($token)) {
            throw new Exception('Token is empty');
        }
        $tokenHash = self::getTokenHash($token);
        $userToken = self::findOne(['token_hash' => $tokenHash]);
        if ($userToken === null) {
            return;
        }
        $userToken->delete();
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getTokenFromBearerHeader(): string
    {
        $pattern = '/^Bearer\s+(.*?)$/';
        $token = Yii::$app->request->headers->get('Authorization');
        if (!is_string($token)) {
            return '';
        }
        if (preg_match($pattern, $token, $matches)) {
            $token = $matches[1];
        } else {
            throw new Exception('Can\'t parse token');
        }
        return $token;
    }

    /**
     * @return bool
     */
    public static function deleteOldTokens(): bool
    {
        self::deleteAll('type = ' . self::TYPE_LOGIN . ' AND created_at < ' . self::LOGIN_INTERVAL);
        self::deleteAll('type = ' . self::TYPE_CHANGE_PASSWORD . ' AND created_at < ' . self::CHANGE_PASSWORD_INTERVAL);
        return true;
    }

    // private methods

    /**
     * @return void
     * @throws Exception
     */
    private function setToken(): void
    {
        $this->token = Yii::$app->security->generateRandomString();
    }

    /**
     * @return void
     */
    private function setTokenHash(): void
    {
        $this->token_hash = self::getTokenHash($this->token);
    }

    /**
     * @param string $token
     * @return string
     */
    private static function getTokenHash(string $token): string
    {
        $salt = 'Bd~₴';
        return crypt($token, $salt);
    }

    /**
     * @param string $token
     * @param int $type
     * @param string $interval
     * @return int|null
     */
    private static function findUserIdByToken(string $token, int $type, string $interval): int|null
    {
        if (empty($token)) {
            return null;
        }
        $tokenHash = self::getTokenHash($token);
        $userToken = self::find()
            ->where(['token_hash' => $tokenHash])
            ->andWhere(['type' => $type])
            ->andWhere('created_at > ' . $interval)
            ->asArray()
            ->one();
        if (empty($userToken)) {
            return null;
        }
        return $userToken['user_id'];
    }
}