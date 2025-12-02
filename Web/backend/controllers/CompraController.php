<?php

namespace backend\controllers;

use common\models\Bilhete;
use common\models\Cinema;
use Yii;
use common\models\Compra;
use backend\models\CompraSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class CompraController extends Controller
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
                        'roles' => ['admin', 'gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['index', 'view', 'confirm-all-tickets'],
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

    public function actionIndex($cinema_id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;
        $gerirCinemas = $currentUser->can('gerirCinemas');

        // CRIAR SEARCH MODEL E RECEBER PARÂMETROS DA QUERY
        $searchModel = new CompraSearch();
        $params = Yii::$app->request->queryParams;

        $cinemaFilterOptions = ArrayHelper::map(Cinema::find()->asArray()->all(), 'id', 'nome');

        // ADMIN (GERE CINEMAS) --> VÊ TODAS AS COMPRAS
        if ($gerirCinemas) {

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
            'cinemaFilterOptions' => $cinemaFilterOptions,
            'estadoFilterOptions' => Compra::optsEstado(),
            'gerirCinemas' => $gerirCinemas,
        ]);
    }


    // ADMIN --> VÊ DETALHES DAS COMPRAS DE QUALQUER CINEMA
    // GERENTE/FUNCIONÁRIO --> VÊ DETALHES DAS COMPRAS DO SEU CINEMA
    public function actionView($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;
        $gerirCinemas = $currentUser->can('gerirCinemas');

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
            'gerirCinemas' => $gerirCinemas,
        ]);
    }


    // ADMIN --> MUDA O ESTADO DE QUALQUER COMPRA
    // GERENTE/FUNCIONÁRIO --> MUDA O ESTADO DA COMPRA SE FOR DO SEU CINEMA
    public function actionChangeStatus($id, $estado)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER COMPRA
        $model = $this->findModel($id);

        // VERIFICAR PERMISSÃO
        if (!Yii::$app->user->can('gerirCompras')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado das compras.');
            return $this->redirect(['index']);
        }

        // VERIFICAR CINEMA (GERENTE/FUNCIONÁRIO --> SÓ O SEU CINEMA)
        if (!$currentUser->can('admin')) {

            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            if ($userCinemaId === null || $userCinemaId != $model->sessao->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para alterar compras de outro cinema.');
                return $this->redirect(['index']);
            }
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

        // SE A SESSÃO JÁ TERMINOU --> NÃO DEIXAR ALTERAR O ESTADO
        if ($model->sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não pode alterar o estado de compras cuja sessão já terminou.');
            return $this->redirect(['index']);
        }

        // SE A COMPRA ESTIVER CANCELADA --> NÃO DEIXAR RE-ATIVAR
        if ($model->isEstadoCancelada()) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar o estado de uma compra já cancelada.');
            return $this->redirect(['index']);
        }

        // ATUALIZAR O ESTADO DA COMPRA
        $model->estado = $estado;

        if ($model->save(false, ['estado'])) {

            // SE A COMPRA FOR CANCELADA --> ENVIAR EMAIL PARA O CLIENTE
            if ($model->enviarEmailEstado($estado)) {
                Yii::$app->session->setFlash('success', 'Estado da compra atualizado e email enviado ao cliente.');
            }
            else {
                Yii::$app->session->setFlash('success', 'Estado da compra atualizado.');
            }

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


    // ADMIN/GERENTE/FUNCIONÁRIO --> MUDA O ESTADO DOS BILHETES PENDENTES ASSOCIADOS À COMPRA
    // GERENTE/FUNCIONÁRIO --> MUDA O ESTADO DOS BILHETES PENDENTES ASSOCIADOS À COMPRA (DO SEU CINEMA)
    public function actionConfirmAllTickets($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER COMPRA
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('validarBilhetes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para confirmar bilhetes.');
            return $this->redirect(['index']);
        }

        // OBTER CINEMA DA COMPRA
        $cinemaId = $model->sessao->cinema_id ?? null;

        // GERENTE/FUNCIONÁRIO --> SÓ PODEM CONFIRMAR COMPRAS DO SEU CINEMA
        if (!$currentUser->can('admin')) {

            // OBTER CINEMA DO USER ATUAL
            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            if ($userCinemaId === null || $userCinemaId != $cinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para confirmar bilhetes de outro cinema.');
                return $this->redirect(['index']);
            }
        }

        // BLOQUEAR SE A SESSÃO JÁ TERMINOU
        if ($model->sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não é possível confirmar bilhetes de uma sessão já iniciada ou terminada.');
            return $this->redirect(['index']);
        }

        // SE TODOS OS BILHETES DA COMPRA JÁ FORAM CONFIRMADOS --> VOLTAR
        if ($model->isTodosBilhetesConfirmados()) {
            Yii::$app->session->setFlash('info', 'Todos os bilhetes desta compra já foram confirmados.');
            return $this->redirect(['index']);
        }

        // OBTER BILHETES ASSOCIADOS À COMPRA
        $bilhetes = $model->bilhetes;
        if (empty($bilhetes)) {
            Yii::$app->session->setFlash('info', 'Esta compra não possui bilhetes.');
            return $this->redirect(['index']);
        }

        // CONFIRMAR TODOS OS BILHETES PENDENTES
        $confirmados = 0;
        foreach ($bilhetes as $bilhete) {
            if ($bilhete->estado !== Bilhete::ESTADO_CONFIRMADO) {
                $bilhete->estado = Bilhete::ESTADO_CONFIRMADO;
                $bilhete->save(false, ['estado']);
                $confirmados++;
            }
        }

        // MENSAGEM FINAL
        if ($confirmados > 0) {
            Yii::$app->session->setFlash('success', "Foram confirmados {$confirmados} bilhetes.");
        }
        else {
            Yii::$app->session->setFlash('info', 'Todos os bilhetes desta compra já estavam confirmados.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['view', 'id' => $model->id]);
    }


    protected function findModel($id)
    {
        if (($model = Compra::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
