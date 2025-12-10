<?php

namespace backend\modules\api\controllers;

use common\models\User;
use common\models\UserProfile;
use Exception;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ],
            'except' => ['login', 'signup'],
        ];

        return $behaviors;
    }

    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;

        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!$username || !$password) {
            throw new BadRequestHttpException('Username e password são obrigatórios.');
        }

        $user = User::findByUsername($username);

        if (!$user || !$user->validatePassword($password) || !$user->isCliente()) {
            throw new UnauthorizedHttpException("Credenciais inválidas.");
        }

        // Gerar access-token se não tiver
        if (!$user->auth_key) {
            $user->generateAuthKey();
            $user->save(false);
        }

        return [
            'status' => 'success',
            'access-token' => $user->auth_key,
            'perfil' => [
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

        // Verificar se todos os campos foram enviados
        $campos = ['username', 'password', 'email', 'nome', 'telemovel'];
        foreach ($campos as $campo) {
            $$campo = $body[$campo] ?? null;

            if (empty($$campo)) {
                throw new BadRequestHttpException("O campo '$campo' é obrigatório.");
            }
        }

        // Criar user
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

        // Criar profile
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

        // Atribuir role RBAC
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('cliente');
        if (!$role) throw new Exception("Role 'cliente' não existe no RBAC.");
        $auth->assign($role, $user->id);

        return [
            'status' => 'success',
            'access-token' => $user->auth_key,
            'perfil' => [
                'id' => $user->id,
                'username' => $user->username,
                'nome' => $user->profile->nome ?? null,
                'email' => $user->email,
                'telemovel' => $user->profile->telemovel ?? null,
            ]
        ];
    }

    public function actionValidateToken()
    {
        $user = Yii::$app->user->identity;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'nome' => $user->profile->nome ?? null,
            'telemovel' => $user->profile->telemovel ?? null,
        ];
    }
}