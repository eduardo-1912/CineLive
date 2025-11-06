<?php

namespace backend\controllers;

use Yii;
use common\models\Compra;
use backend\models\CompraSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CompraController implements the CRUD actions for Compra model.
 */
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
                        'roles' => ['admin', 'gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['index', 'view'],
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

    /**
     * Lists all Compra models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompraSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Compra model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $bilhetesDataProvider = new \yii\data\ActiveDataProvider([
            'query' => $model->getBilhetes(),
            'pagination' => false,
        ]);

        return $this->render('view', [
            'model' => $model,
            'bilhetesDataProvider' => $bilhetesDataProvider,
        ]);
    }


    // MUDAR O ESTADO DA COMPRA
    public function actionChangeStatus($id, $estado)
    {
        $model = $this->findModel($id);

        // 🔒 Permissões
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('gerirCompras')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado das compras.');
            return $this->redirect(['index']);
        }

        // ⚠️ Estados válidos
        $estadosValidos = array_keys(\common\models\Compra::optsEstado());
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // 🚫 Se já estiver no estado pretendido
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', 'A compra já se encontra neste estado.');
            return $this->redirect(['index']);
        }

        // 🚫 Impedir voltar a "pendente" se já estiver confirmada ou cancelada
        if ($estado === \common\models\Compra::ESTADO_PENDENTE &&
            in_array($model->estado, [\common\models\Compra::ESTADO_CONFIRMADA, \common\models\Compra::ESTADO_CANCELADA])) {
            Yii::$app->session->setFlash('warning', 'Não é possível alterar uma compra confirmada ou cancelada para pendente.');
            return $this->redirect(['index']);
        }

        // 🧾 Atualizar estado da compra
        $model->estado = $estado;

        if ($model->save(false, ['estado'])) {
            // 🔄 Atualizar bilhetes associados
            foreach ($model->bilhetes as $bilhete) {
                if ($estado === \common\models\Compra::ESTADO_CANCELADA) {
                    $bilhete->estado = \common\models\Bilhete::ESTADO_CANCELADO;
                    $bilhete->save(false, ['estado']);
                } elseif ($estado === \common\models\Compra::ESTADO_CONFIRMADA) {
                    // Só confirmar bilhetes que estavam pendentes
                    if ($bilhete->estado === \common\models\Bilhete::ESTADO_PENDENTE) {
                        $bilhete->estado = \common\models\Bilhete::ESTADO_CONFIRMADO;
                        $bilhete->save(false, ['estado']);
                    }
                }
            }

            // ✅ Mensagem de sucesso personalizada
            $mensagem = match ($estado) {
                \common\models\Compra::ESTADO_CONFIRMADA => 'Compra confirmada e bilhetes ativados com sucesso.',
                \common\models\Compra::ESTADO_CANCELADA => 'Compra cancelada e bilhetes anulados.',
                default => 'Estado da compra atualizado com sucesso.',
            };
            Yii::$app->session->setFlash('success', $mensagem);

        } else {
            Yii::$app->session->setFlash('error', 'Erro ao atualizar o estado da compra.');
        }

        return $this->redirect(['index']);
    }



    /**
     * Finds the Compra model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Compra the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Compra::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
