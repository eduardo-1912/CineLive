<?php

namespace frontend\controllers;

use yii\web\Controller;

class ServicosController extends Controller
{
    public function actionIndex(){

        return $this->render('index');
    }
}