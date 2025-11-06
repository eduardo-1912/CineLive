<?php

namespace frontend\controllers;

use frontend\models\ContactForm;
use Yii;
use yii\base\Model;
use yii\web\Controller;

class ServicosController extends Controller
{
    public function actionIndex(){

        $model = new ContactForm();
        return $this->render('index', ['model' => $model]);
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}