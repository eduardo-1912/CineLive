<?php

namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\MethodNotAllowedHttpException;

class CinemaController extends ActiveController
{
    public $modelClass = 'common\models\Cinema';

    public function actions()
    {
        $actions = parent::actions();

        // Bloquear métodos não autorizados
        $notAllowedActions = ['create', 'update', 'delete'];

        foreach ($notAllowedActions as $action) {
            $actions[$action] = fn() => throw new MethodNotAllowedHttpException;
        }

        return $actions;
    }
}