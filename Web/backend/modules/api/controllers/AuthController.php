<?php

namespace backend\modules\api\controllers;

use common\models\User;
use common\models\UserProfile;
use Exception;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;

        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!$username || !$password) {
            throw new BadRequestHttpException('Username e password são obrigatórios.');
        }

        $user = User::findByUsername($username);

        if (!$user || !$user->validatePassword($password)) {
            throw new UnauthorizedHttpException("Credenciais inválidas.");
        }

        // Gerar access-token se não tiver
        if (!$user->auth_key) {
            $user->generateAuthKey();
            $user->save(false);
        }

        return [
            'access-token' => $user->auth_key,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nome' => $user->profile->nome ?? null,
                'email' => $user->email,
                'telemovel' => $user->profile->telemovel ?? null,
            ]
        ];
    }

    public function actionSignup()
    {
        $body = Yii::$app->request->bodyParams;

        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;
        $email = $body['email'] ?? null;
        $nome = $body['nome'] ?? null;
        $telemovel = $body['telemovel'] ?? null;

        if (!$username || !$password || !$email || !$nome || !$telemovel) {
            throw new BadRequestHttpException('Faltam campos obrigatórios.');
        }

        // 1. Criar User
        $user = new User();
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->status = $user::STATUS_ACTIVE;

        if (!$user->save()) {
            return [
                'status' => 'error',
                'errors' => $user->errors
            ];
        }

        // 2. Criar Profile
        $profile = new UserProfile();
        $profile->user_id = $user->id;
        $profile->nome = $nome;
        $profile->telemovel = $telemovel;

        if (!$profile->save()) {
            $user->delete();

            return [
                'status' => 'error',
                'errors' => $profile->errors
            ];
        }

        // 3. Atribuir role 'cliente'
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('cliente');

        if (!$role) {
            throw new Exception("Role 'cliente' não existe no RBAC.");
        }

        $auth->assign($role, $user->id);

        return [
            'status' => 'success',
            'access-token' => $user->auth_key,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nome' => $user->profile->nome ?? null,
                'email' => $user->email,
                'telemovel' => $user->profile->telemovel ?? null,
            ]
        ];
    }
}