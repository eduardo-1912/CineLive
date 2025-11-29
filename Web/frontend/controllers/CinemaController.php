<?php

namespace frontend\controllers;

use common\models\Cinema;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CinemaController extends Controller
{
    public function actionIndex()
    {
        $cinemas = Cinema::findAtivos();

        return $this->render('index', [
            'cinemas' => $cinemas,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Cinema::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}