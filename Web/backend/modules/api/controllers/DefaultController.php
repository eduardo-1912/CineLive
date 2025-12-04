<?php

namespace backend\modules\api\controllers;

use yii\rest\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return [
            'status' => 'success',
            'time' => time(),
            'message' => 'CineLive API',
        ];
    }
}