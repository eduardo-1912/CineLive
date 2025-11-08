<?php

namespace backend\controllers;

use common\models\Bilhete;
use Yii;
use common\models\Compra;
use backend\models\CompraSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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

    // ADMIN --> VÊ AS COMPRAS DE QUALQUER CINEMA
    // GERENTE/FUNCIONÁRIO --> VÊ AS COMPRAS DO SEU CINEMA
    public function actionIndex($cinema_id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // CRIAR SEARCH MODEL E RECEBER PARÂMETROS DA QUERY
        $searchModel = new CompraSearch();
        $params = Yii::$app->request->queryParams;

        // ADMIN --> VÊ TODAS AS COMPRAS
        if ($currentUser->can('admin')) {

            // SE FOI PASSADO CINEMA_ID VIA PARÂMETRO
            if ($cinema_id !== null) {
                $params['CompraSearch']['cinema_id'] = $cinema_id;
            }

            $dataProvider = $searchModel->search($params);
        }

        // GERENTE/FUNCIONÁRIO --> APENAS AS COMPRAS DO SEU CINEMA
        else {

            // OBTER PERFIL DO USER ATUAL
            $userProfile = $currentUser->identity->profile ?? null;

            // VERIFICAR SE TEM CINEMA ASSOCIADO
            if (!$userProfile || !$userProfile->cinema_id) {
                throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
            }

            // SE TENTAR PASSAR CINEMA_ID NA URL --> REDIRECIONAR
            if ($cinema_id !== null) {
                return $this->redirect(['index']);
            }

            // APLICAR FILTRO DO SEU CINEMA
            $params['CompraSearch']['cinema_id'] = $userProfile->cinema_id;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    // ADMIN --> VÊ DETALHES DAS COMPRAS DE QUALQUER CINEMA
    // GERENTE/FUNCIONÁRIO --> VÊ DETALHES DAS COMPRAS DO SEU CINEMA
    public function actionView($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER COMPRA
        $model = $this->findModel($id);

        // SE FOR GERENTE/FUNCIONÁRIIO --> SÓ VÊ COMPRAS DO SEU CINEMA
        if (!$currentUser->can('admin')) {

            // OBTER CINEMA DO USER ATUAL
            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            // OBTER CINEMA DA COMPRA (via sessão da compra)
            $compraCinemaId = $model->sessao->cinema_id ?? null;

            // SE USER NÃO TIVER CINEMA OU FOR DIFERENTE DO DA COMPRA --> SEM PERMISSÃO
            if (!$userCinemaId || $userCinemaId != $compraCinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta compra.');
                return $this->redirect(['index']);
            }
        }

        // LISTAR OS BILHETES ASSOCIADOS À COMPRA
        $bilhetesDataProvider = new ActiveDataProvider([
            'query' => $model->getBilhetes(),
            'pagination' => false,
        ]);

        return $this->render('view', [
            'model' => $model,
            'bilhetesDataProvider' => $bilhetesDataProvider,
        ]);
    }


    // ADMIN/GERENTE --> MUDA O ESTADO DA COMPRA
    public function actionChangeStatus($id, $estado)
    {
        $model = $this->findModel($id);

        // VERIFICAR PERMISSÃO
        if (!Yii::$app->user->can('gerirCompras')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado das compras.');
            return $this->redirect(['index']);
        }

        // VERIFICAR QUE O ESTADO É VÁLIDO
        $estadosValidos = array_keys(Compra::optsEstado());
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', 'A compra já se encontra neste estado.');
            return $this->redirect(['index']);
        }

        // ATUALIZAR O ESTADO DA COMPRA
        $model->estado = $estado;

        if ($model->save(false, ['estado'])) {

            // ATUALIZAR O ESTADO DOS BILHETES
            foreach ($model->bilhetes as $bilhete) {

                // SE COMPRA FOR CANCELADA --> CANCELAR OS SEUS BILHETES
                if ($estado === Compra::ESTADO_CANCELADA) {
                    $bilhete->estado = Bilhete::ESTADO_CANCELADO;
                    $bilhete->save(false, ['estado']);
                    Yii::$app->session->setFlash('success', 'Compra cancelada e bilhetes anulados.');
                }
                // SE COMPRA FOR RE-CONFIRMADA --> COLOCAR BILHETES EM ESTADO PENDENTE
                elseif ($estado === Compra::ESTADO_CONFIRMADA) {
                    $bilhete->estado = Bilhete::ESTADO_PENDENTE;
                    $bilhete->save(false, ['estado']);
                    Yii::$app->session->setFlash('success', 'Compra re-confirmada e bilhetes colocados em estado pendente.');
                }
            }
        }
        else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar o estado da compra.');
        }

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = Compra::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
