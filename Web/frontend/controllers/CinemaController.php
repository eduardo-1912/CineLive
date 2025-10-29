<?php

namespace frontend\controllers;

use Yii;
use common\models\Cinema;
use yii\web\Controller;

class CinemaController extends Controller
{
    public function actionIndex(){
        $cinemas = Cinema::find()->all();
        return $this->render('index', ['cinemas' => $cinemas]);
    }

}