<?php

namespace frontend\controllers;

use common\models\Compra;
use common\models\Sessao;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

class CompraController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionCreate($sessao_id){

        $sessao = Sessao::findOne($sessao_id);
        return $this->render('create', ['sessao' => $sessao]);
    }

    protected function findModel($id)
    {
        if (($model = Compra::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}