<?php

namespace backend\controllers;

use common\models\Cinema;
use Yii;
use common\models\Sala;
use backend\models\SalaSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SalaController implements the CRUD actions for Sala model.
 */
class SalaController extends Controller
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
                    [
                        'allow' => true,
                        'roles' => ['gerente'],
                        'actions' => ['create', 'update', 'activate', 'deactivate']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['index', 'view']
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

    // ADMIN --> VÊ TODAS AS SALAS
    // GERENTE/FUNCIONÁRIO --> VÊ AS SALAS DO SEU CINEMA
    public function actionIndex($cinema_id = null)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // CRIAR SEARCH MODEL E QUERY NA DB
        $searchModel = new SalaSearch();
        $params = Yii::$app->request->queryParams;

        // ADMIN --> VÊ TODAS AS SALAS
        if ($currentUser->can('admin')) {

            // SE ALGUM CINEMA FOI PASSADO COM PARÂMETRO
            if ($cinema_id !== null) {
                $params['SalaSearch']['cinema_id'] = $cinema_id;
            }

            $dataProvider = $searchModel->search($params);
        }

        // GERENTE/FUNCIONÁRIO --> APENAS VÊ OS SALAS DO SEU CINEMA
        else {
            // OBTER PERFIL DO USER ATUAL
            $userProfile = $currentUser->identity->profile;

            // VERIFICAR SE TEM CINEMA ASSOCIADO
            if (!$userProfile || !$userProfile->cinema_id) {
                throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
            }

            if ($cinema_id !== null) {
                $this->redirect(['index']);
            }

            // APLICAR FILTRO DO SEU CINEMA
            $params['SalaSearch']['cinema_id'] = $userProfile->cinema_id;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cinemaId' => $cinema_id,
        ]);
    }


    // ADMIN --> VÊ DETALHES DE TODAS AS SALAS
    // GERENTE/FUNCIONÁRIO --> APENAS VÊ AS SUAS SALAS
    public function actionView($id)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // SE É GERENTE/FUNCIONÁRIO --> APENAS VÊ SALAS DO SEU CINEMA
        if (!$currentUser->can('admin')) {

            // OBTER ID DO CINEMA DO USER ATUAL
            $cinemaId = $currentUser->identity->profile->cinema_id;

            // OBTER SALA SELECIONADA
            $model = $this->findModel($id);

            // SE CINEMA DO USER E CINEMA DA SALA FOREM DIFERENTES --> SEM ACESSO
            if ($cinemaId != $model->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta sala.');
                return $this->redirect('index');
            }
        }

        // SE É ADMIN OU UTILIZADOR É DO MESMO CINEMA DA SALA --> TEM ACESSO
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    // ADMIN --> CRIA SALAS PARA QUALQUER CINEMA
    // GERENTE --> APENAS CRIA SALAS PARA O SEU CINEMA
    public function actionCreate($cinema_id = null)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAÇÃO DE PERMISSÕES
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para criar salas.');
        }

        // CRIAR NOVA SALA
        $model = new Sala();

        // SE FOR GERENTE --> FORÇAR ATRIBUIÇÃO CINEMA_ID DO GERENTE
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {
            $model->cinema_id = $currentUser->identity->profile->cinema_id;
            $cinema_id = $model->cinema_id;
        }

        // SE UM CINEMA FOI PASSADO POR PARÂMETRO
        elseif ($cinema_id) {
            // OBTER O CINEMA
            $cinema = Cinema::findOne($cinema_id);

            // SE CINEMA ESTIVER ENCERRADO --> REDIRECIONAR
            if (!$cinema || $cinema->estado === Cinema::ESTADO_ENCERRADO) {
                Yii::$app->session->setFlash('error', 'Não é possível criar salas para um cinema encerrado.');
                return $this->redirect(['create']);
            }

            // CASO CONTRÁRIO, ATRIBUI O CINEMA AO MODELO
            $model->cinema_id = $cinema_id;
        }

        // SE JÁ TIVERMOS UM CINEMA --> CALCULAR O NÚMERO DA SALA NOVA
        $proximoNumero = $cinema_id ? Sala::getProximoNumeroPorCinema($cinema_id) : null;

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {

            // SE O NÚMERO DA SALA AINDA NÃO VIER PREENCHIDO
            if (empty($model->numero) && $model->cinema_id) {
                $model->numero = Sala::getProximoNumeroPorCinema($model->cinema_id);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sala criada com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar a sala.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'proximoNumero' => $proximoNumero,
        ]);
    }


    // ADMIN --> EDITA QUALQUER SALA
    // GERENTE --> APENAS EDITA SALAS DO SEU CINEMA
    public function actionUpdate($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar salas.');
            return $this->redirect(['index']);
        }

        // OBTER SALA SELECIONADA
        $model = $this->findModel($id);

        // SE É GERENTE --> SÓ PODE EDITAR SALAS DO SEU CINEMA
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {

            // OBTER ID DO CINEMA DO USER
            $userCinemaId = $currentUser->identity->profile->cinema_id;

            // SE CINEMAS NÃO COINCIDEM --> SEM PERMISSÃO
            if ($model->cinema_id != $userCinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para editar salas de outro cinema.');
                return $this->redirect(['index']);
            }
        }

        // OBTER DADOS ORIGINAIS
        $anterior = $this->findModel($id);

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {

            // NÃO DEIXAR ALTERAR O CINEMA DA SALA
            $model->cinema_id = $anterior->cinema_id;

            // SE NÃO PODER SER ENCERRADA --> NÃO DEIXAR ALTERAR ESTES DADOS
            if (!$model->isClosable()) {
                $model->num_filas = $anterior->num_filas;
                $model->num_colunas = $anterior->num_colunas;
                $model->estado = $anterior->estado;
            }

            // FORÇAR CINEMA_ID SE FOR GERENTE
            if ($currentUser->can('gerente') && !$currentUser->can('admin')) {
                $model->cinema_id = $currentUser->identity->profile->cinema_id;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sala atualizada com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar a sala.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    // ADMIN/GERENTE --> MUDAR O ESTADO DA SALA
    public function actionChangeStatus($id, $estado)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado das salas.');
            return $this->redirect(['index']);
        }

        // OBTER SALA
        $model = $this->findModel($id);

        // VERIFICAR CINEMA (GERENTE SÓ PODE ALTERAR AS SUAS SALAS)
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {
            $userCinemaId = $currentUser->identity->profile->cinema_id;

            // SE SALA NÃO FOR DO CINEMA DO GERENTE --> MENSAGEM DE ERRO
            if ($model->cinema_id != $userCinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para alterar salas de outro cinema.');
                return $this->redirect(['index']);
            }
        }

        // OBTER ESTADOS VÁLIDOS
        $estadosValidos = array_keys(Sala::optsEstado());

        // SE ESTADO NÃO FOR VÁLIDO --> MENSAGEM DE ERRO
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', "O cinema já se encontra no estado selecionado.");
            return $this->redirect(['index']);
        }

        if ($estado === Sala::ESTADO_ENCERRADA) {
            // SE SALA JÁ ESTIVER ENCERRADA --> MENSAGEM DE AVISO
            if ($model->isActivatable()) {
                Yii::$app->session->setFlash('info', 'Esta sala já se encontra encerrada.');
                return $this->redirect(['index']);
            }

            // SE TIVER SESSÕES ATIVAS --> NÃO DEIXAR ENCERRAR
            if (!$model->isClosable()) {
                Yii::$app->session->setFlash('error', 'Não é possível encerrar esta sala pois existem sessões ativas ou com bilhetes associadas.');
                return $this->redirect(['index']);
            }
        }

        // ALTERAR ESTADO
        $model->estado = $estado;

        if ($model->save(false, ['estado'])) {
            $mensagem = $estado === Sala::ESTADO_ATIVA ? 'Sala ativada com sucesso.' : 'Sala encerrada com sucesso.';
            Yii::$app->session->setFlash('success', $mensagem);
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao alterar o estado da sala.');
        }

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = Sala::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
