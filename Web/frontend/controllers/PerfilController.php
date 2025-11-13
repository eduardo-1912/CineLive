<?php

namespace frontend\controllers;

use common\models\AluguerSala;
use common\models\Compra;
use common\models\User;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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

    // DADOS DO PERFIL, COMPRAS E ALUGUERES MAIS RECENTES
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


    // ELIMINAR A SUA CONTA
    public function actionDeleteAccount()
    {
        $currentUser = Yii::$app->user;

        $model = $this->findModel($currentUser->id);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // REMOVER TODAS AS ROLES/PERMISSÕES DO USER
            Yii::$app->authManager->revokeAll($model->id);

            // APAGAR PERFIL
            if ($model->profile) {
                if (!$model->profile->delete()) {
                    throw new \Exception("Erro ao eliminar o perfil.");
                }
            }

            // APAGAR UTILIZADOR
            if (!$model->delete()) {
                throw new \Exception("Erro ao eliminar o utilizador.");
            }

            // DAR COMMIT NA TRANSAÇÃO
            $transaction->commit();

            // TERMINAR SESSÃO
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('success', 'A sua conta foi eliminada com sucesso.');
            return $this->goHome();

        }
        catch (Exception $e) {
            $transaction->rollBack();
            Yii::error("Erro ao eliminar conta: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar a sua conta. Tente novamente mais tarde.');
            return $this->redirect(['index']);
        }
    }


    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}