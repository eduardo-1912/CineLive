<?php

namespace backend\controllers;

use common\models\UserExtension;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;
use common\models\UserProfile;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // qualquer utilizador autenticado
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $user = UserExtension::findOne($userId);
        $profile = $user->profile ?? null;

        return $this->render('index', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}
