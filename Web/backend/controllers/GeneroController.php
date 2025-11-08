<?php

namespace backend\controllers;

use Yii;
use common\models\Genero;
use backend\models\GeneroSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GeneroController implements the CRUD actions for Genero model.
 */
class GeneroController extends Controller
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
                        'roles' => ['admin'],
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

    // ADMIN --> VÊ E CRIA GÉNEROS
    public function actionIndex()
    {
        $searchModel = new GeneroSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // CRIAR NOVO GÉNERO
        $model = new Genero();

        // GUARDAR NOVO GÉNERO
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Género criado com sucesso.');
            return $this->redirect(['index']);
        }

        // PASSAR O MODELO PARA O FORM
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    // ADMIN --> EDITA UM GÉNERO
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    // ADMIN --> ELIMINA GÉNERO
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = Genero::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
