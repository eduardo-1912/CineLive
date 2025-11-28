<?php

namespace frontend\controllers;

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
        $model = Yii::$app->user->identity;

        $edit = Yii::$app->request->get('edit') == 1;

        $compras = $model->getCompras()->orderBy(['id' => SORT_DESC])->limit(3)->all();
        $alugueres = $model->getAlugueres()->orderBy(['id' => SORT_DESC])->limit(2)->all();

        if ($model->load(Yii::$app->request->post()) && $model->profile->load(Yii::$app->request->post())) {
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


    public function actionDeleteAccount()
    {
        $currentUser = Yii::$app->user;

        $model = $this->findModel($currentUser->id);

        Yii::$app->authManager->revokeAll($model->id);

        if ($model->profile && $model->profile->delete()) {
            if ($model->delete()) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('success', 'A sua conta foi eliminada com sucesso.');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Erro ao eliminar a sua conta.');
        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}