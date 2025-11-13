<?php

namespace frontend\controllers;

use common\models\AluguerSala;
use common\models\Compra;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PerfilController extends Controller
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
                        'roles' => ['cliente'],
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

    public function actionIndex()
    {
        $currentUser = Yii::$app->user;

        $model = $this->findModel($currentUser->id);
        $edit = Yii::$app->request->get('edit') == 1;

        $compras = Compra::find()->where(['cliente_id' => $currentUser->id])
            ->orderBy(['id' => SORT_DESC])->limit(3)->all();

        $alugueres = AluguerSala::find()->where(['cliente_id' => $currentUser->id])
            ->orderBy(['id' => SORT_DESC])->limit(2)->all();


        // SE ESTÁ A EDITAR O PERFIL
        if ($model->load(Yii::$app->request->post()) && $model->profile->load(Yii::$app->request->post())) {

            // GUARDAR
            if ($model->save(false) && $model->profile->save()) {
                Yii::$app->session->setFlash('success', 'Dados atualizados com sucesso.');
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar os dados.');
        }

        return $this->render('index', [
            'model' => $model,
            'edit' => $edit,
            'compras' => $compras,
            'alugueres' => $alugueres,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}