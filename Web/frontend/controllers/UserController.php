<?php

namespace frontend\controllers;

use common\models\User;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public function actionIndex() {
        $userId = Yii::$app->user->id;
        $user = User::findOne($userId);
        $profile = $user->profile ?? null;

        return $this->render('index', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}