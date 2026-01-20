<?php

namespace backend\modules\api\controllers;

use yii\rest\Controller;

class MatematicaController extends Controller
{
    public function actionRaizdois()
    {
        $resultado = round(sqrt(2), 2);
        return ['raizdois' => $resultado];
    }
}