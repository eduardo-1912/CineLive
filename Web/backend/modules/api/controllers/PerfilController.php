<?php

namespace backend\modules\api\controllers;

use Throwable;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;

class PerfilController extends Controller
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
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'nome' => $user->profile->nome ?? null,
            'email' => $user->email,
            'telemovel' => $user->profile->telemovel ?? null,
        ];
    }

    public function actionUpdate()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new UnauthorizedHttpException("Token inválido.");
        }

        $body = Yii::$app->request->bodyParams;

        // 1. Atualizar user
        if (isset($body['username'])) {
            $user->username = $body['username'];
        }

        if (isset($body['email'])) {
            $user->email = $body['email'];
        }

        // Password é opcional
        if (!empty($body['password'])) {
            $user->password = $body['password'];
        }

        if (!$user->save()) {
            return [
                'status' => 'error',
                'errors' => $user->errors
            ];
        }

        // 2. Atualizar profile
        $profile = $user->profile;

        if (isset($body['nome'])) {
            $profile->nome = $body['nome'];
        }

        if (isset($body['telemovel'])) {
            $profile->telemovel = $body['telemovel'];
        }

        if (!$profile->save()) {
            return [
                'status' => 'error',
                'errors' => $profile->errors
            ];
        }

        return [
            'status' => 'success',
            'message' => "Perfil atualizado com sucesso.",
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nome' => $profile->nome,
                'email' => $user->email,
                'telemovel' => $profile->telemovel,
            ]
        ];
    }

    public function actionDelete()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new UnauthorizedHttpException("Token inválido.");
        }

        // Remover RBAC assignments
        Yii::$app->authManager->revokeAll($user->id);

        // Remover profile
        if ($user->profile && !$user->profile->delete()) {
            return [
                'status' => 'error',
                'message' => 'Erro ao eliminar o perfil.'
            ];
        }

        // Remover user
        if (!$user->delete()) {
            return [
                'status' => 'error',
                'message' => 'Erro ao eliminar utilizador.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Conta eliminada com sucesso.'
        ];
    }
}