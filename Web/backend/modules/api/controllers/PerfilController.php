<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;
use common\models\UserProfile;

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
            throw new UnauthorizedHttpException("Credenciais inválidas.");
        }

        $body = Yii::$app->request->bodyParams;

        // Atualizar user
        $user->load($body, '');

        if (!$user->save()) {
            return [
                'status' => 'error',
                'errors' => $user->errors
            ];
        }

        // Criar profile se não tiver
        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);

        // Atualizar profile
        $profile->load($body, '');

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
                'nome' => $profile->nome ?? null,
                'email' => $user->email,
                'telemovel' => $profile->telemovel ?? null,
            ]
        ];
    }

    public function actionDelete()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new UnauthorizedHttpException("Credenciais inválidas.");
        }

        // Remover RBAC assignments
        Yii::$app->authManager->revokeAll($user->id);

        // Eliminar profile
        if ($user->profile && !$user->profile->delete()) {
            return [
                'status' => 'error',
                'message' => 'Erro ao eliminar o perfil.'
            ];
        }

        // Eliminar user
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