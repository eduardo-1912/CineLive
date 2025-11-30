<?php

namespace frontend\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
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
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['cliente'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($edit = false)
    {
        $currentUser = Yii::$app->user;
        $model = $currentUser->identity;

        if (!$currentUser->can('verPerfil', ['model' => $model])) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver este perfil.');
            return $this->goHome();
        }

        // Obter compras e alugueres mais recentes
        $compras = $model->getCompras()->orderBy(['id' => SORT_DESC])->limit(3)->all();
        $alugueres = $model->getAluguerSalas()->orderBy(['id' => SORT_DESC])->limit(2)->all();

        // Editar perfil
        if ($model->load(Yii::$app->request->post()) && $model->profile->load(Yii::$app->request->post())) {

            if (!Yii::$app->user->can('editarPerfil', ['model' => $model])) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para editar este perfil.');
            }

            if ($model->save(false) && $model->profile->save()) {
                Yii::$app->session->setFlash('success', 'Dados atualizados com sucesso.');
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('error', 'Erro ao atualizar os dados.');
        }

        return $this->render('index', [
            'model' => $model,
            'edit' => $edit,
            'compras' => $compras,
            'alugueres' => $alugueres,
        ]);
    }

    public function actionDelete()
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($currentUser->id);

        if (!$currentUser->can('eliminarPerfil', ['model' => $model])) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar esta conta.');
            return $this->goHome();
        }

        if ($model->profile && $model->profile->delete()) {
            Yii::$app->authManager->revokeAll($model->id);

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