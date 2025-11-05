<?php

namespace frontend\controllers;

use frontend\models\ContactForm;
use Yii;
use yii\base\Model;
use yii\web\Controller;

class ServicosController extends Controller
{
    public function actionIndex(){

        return $this->render('index');
    }
    public function ActionValidateForm(){

        $model = new ContactForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('index', ['model' => $model]);
    }
}