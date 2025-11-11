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

    public function actionCreate($sessao_id)
    {
        // OBTER A SESSÃO
        $sessao = Sessao::findOne($sessao_id);

        // LER LUGARES DO URL
        $lugaresSelecionados = Yii::$app->request->get('lugares', '');
        $lugaresSelecionados = array_filter(explode(',', $lugaresSelecionados));

        // CALCULAR TOTAL
        $total = 0;
        if (!empty($lugaresSelecionados)) {
            $total = count($lugaresSelecionados) * (float)$sessao->sala->preco_bilhete;
        }

        return $this->render('create', [
            'sessao' => $sessao,
            'lugaresSelecionados' => $lugaresSelecionados,
            'total' => $total,
        ]);
    }



    protected function findModel($id)
    {
        if (($model = Compra::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}