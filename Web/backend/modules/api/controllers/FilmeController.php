<?php

namespace backend\modules\api\controllers;

use yii\rest\ActiveController;

class FilmeController extends ActiveController
{
    public $modelClass = 'common\models\Filme';

    public function actions()
    {
        $actions = parent::actions();

        // APENAS PERMITIR CONSULTA
        unset(
            $actions['create'],
            $actions['update'],
            $actions['delete'],
            $actions['options'],
        );

        return $actions;
    }
}