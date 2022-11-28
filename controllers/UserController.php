<?php

namespace app\controllers;

use app\interfaces\TokenInterface;
use app\interfaces\UserInterface;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\di\Container;
use yii\di\NotInstantiableException;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;


class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    /**
     * {@inheritdoc}
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['login', 'registration', 'logout', 'ping', 'forgot-password', 'change-password'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * @return array{authenticator: array{class: string, except: string[]}}
     */
    public function behaviors(): array
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => [Yii::$app->params['frontUrl']],
                    'Access-Control-Request-Method' => ['POST', 'GET'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['login', 'registration', 'forgot-password', 'change-password']
            ]
        ];


    }


    /**
     * @return array{token:string}
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    public function actionRegistration(): array
    {
        $email = Yii::$app->request->post('email');
        $password = Yii::$app->request->post('password');
        if (!is_string($password) || !is_string($email)) {
            throw new BadRequestHttpException('Email/password not found');
        }
        $user = $this->getUserModel();
        $user::create($email, $password);
        $token = $user::login($email, $password);

        return [
            'token' => $token,
        ];
    }

    /**
     * @return array{token:string}
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException|ServerErrorHttpException
     */
    public function actionLogin(): array
    {
        $email = Yii::$app->request->post('email');
        $password = Yii::$app->request->post('password');
        if (!is_string($password) || !is_string($email)) {
            throw new BadRequestHttpException('Email/password not found');
        }
        $user = $this->getUserModel();
        $token = $user::login($email, $password);
        return [
            'token' => $token,
        ];
    }

    /**
     * @return array{}
     * @throws Exception
     * @throws Throwable
     */
    public function actionLogout(): array
    {
        $userToken = $this->getUserTokenModel();
        $token = $userToken::getTokenFromBearerHeader();
        $userToken::deleteToken($token);

        return [];
    }

    /**
     * @return array{}
     */
    public function actionPing(): array
    {
        return [];
    }

    /**
     * @return array{}
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException|ServerErrorHttpException
     */
    public function actionForgotPassword(): array
    {
        $email = Yii::$app->request->post('email');
        if (!is_string($email)) {
            throw new BadRequestHttpException('Email not found');
        }
        $user = $this->getUserModel();
        $user::forgotPasswordSendEmail($email);

        return [];
    }


    /**
     * @param string $token
     * @return array{}
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotInstantiableException|ServerErrorHttpException
     */
    public function actionChangePassword(string $token): array
    {
        $password = Yii::$app->request->post('password');
        if (!is_string($password)) {
            throw new BadRequestHttpException('Password not found');
        }
        $user = $this->getUserModel();
        $user::changePassword($token, $password);

        return [];
    }


    /**
     * @return UserInterface
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    private function getUserModel(): UserInterface
    {
        $container = new Container();
        $user = $container->get($this->modelClass);
        if ($user instanceof UserInterface) {
            return $user;
        } else {
            throw new ServerErrorHttpException('Incorrect container');
        }
    }

    /**
     * @return TokenInterface
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ServerErrorHttpException
     */
    private function getUserTokenModel(): TokenInterface
    {
        $container = new Container();
        $userToken = $container->get('app\models\UserToken');
        if ($userToken instanceof TokenInterface) {
            return $userToken;
        } else {
            throw new ServerErrorHttpException('Incorrect container');
        }
    }
}