<?php

namespace frontend\controllers;

use common\models\UserExtension;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public function actionIndex() {
        $userId = Yii::$app->user->id;
        $user = UserExtension::findOne($userId);
        $profile = $user->profile ?? null;

        return $this->render('index', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}