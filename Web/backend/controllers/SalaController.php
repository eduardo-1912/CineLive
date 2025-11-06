<?php

namespace backend\controllers;

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

    /**
     * Lists all Sala models.
     * @return mixed
     */
    public function actionIndex($cinema_id = null)
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('funcionario')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para aceder a esta página.');
            return $this->redirect(['index']);
        }

        // CRIAR SEARCH MODEL E QUERY NA DB
        $searchModel = new SalaSearch();
        $params = Yii::$app->request->queryParams;

        // SE FOR ADMIN --> VÊ TODOS OS UTILIZADORES
        if ($user->can('admin')) {
            if ($cinema_id !== null) {
                $params['SalaSearch']['cinema_id'] = $cinema_id;
            }
            $dataProvider = $searchModel->search($params);
        }

        // SE FOR GERENTE/FUNCIONÁRIO --> APENAS VÊ OS SALAS DO SEU CINEMA
        else {
            // OBTER PERFIL DO USER ATUAL
            $userProfile = $user->identity->profile;

            // VERIFICAR SE TEM CINEMA ASSOCIADO
            if (!$userProfile || !$userProfile->cinema_id) {
                throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
            }

            if ($cinema_id !== null) {
                $this->redirect(['index']);
            }

            // APLICAR FILTRO DE CINEMA
            $params['SalaSearch']['cinema_id'] = $userProfile->cinema_id;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cinemaId' => $cinema_id,
        ]);
    }

    /**
     * Displays a single Sala model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('funcionario')) {
            throw new ForbiddenHttpException('Não tem permissão para aceder a esta página.');
        }

        if (!$user->can('admin')) {
            // OBTER ID DO CINEMA DO USER ATUAL
            $cinemaId = $user->identity->profile->cinema_id;

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

    /**
     * Creates a new Sala model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAÇÃO DE PERMISSÕES
        if (!$user->can('admin') && !$user->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para criar salas.');
        }

        // CRIAR NOVA SALA
        $model = new Sala();

        // SE FOR GERENTE --> FORÇAR ATRIBUIÇÃO CINEMA_ID DO GERENTE
        if ($user->can('gerente') && !$user->can('admin')) {
            $model->cinema_id = $user->identity->profile->cinema_id;
        }

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sala criada com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao criar a sala.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * Updates an existing Sala model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // ATUALIZAR SALA
    public function actionUpdate($id)
    {
        // OBTER USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('admin') && !$user->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para atualizar salas.');
        }

        // OBTER SALA
        $model = $this->findModel($id);

        // SE É GERENTE --> SÓ PODE EDITAR UMA SALA DO SEU CINEMA
        if ($user->can('gerente') && !$user->can('admin')) {

            // OBTER ID DO CINEMA DO USER
            $userCinemaId = $user->identity->profile->cinema_id;

            // SE CINEMAS NÃO COINCIDEM --> SEM PERMISSÃO
            if ($model->cinema_id != $userCinemaId) {
                throw new ForbiddenHttpException('Não tem permissão para editar salas de outro cinema.');
            }
        }

        // OBTER DADOS ORIGINAIS
        $anterior = $this->findModel($id);
        $estadoAnterior = $model->estado;

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {

            if (!$model->isClosable()) {
                // NÃO DEIXAR ALTERAR ESTES DADOS
                $model->cinema_id = $anterior->cinema_id;
                $model->num_filas = $anterior->num_filas;
                $model->num_colunas = $anterior->num_colunas;
                $model->estado = $estadoAnterior;
            }

            // FORÇAR CINEMA_ID SE FOR GERENTE
            if ($user->can('gerente') && !$user->can('admin')) {
                $model->cinema_id = $user->identity->profile->cinema_id;
            }

            // GUARDAR
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sala atualizada com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar a sala.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    // MUDAR O ESTADO DA SALA
    public function actionChangeStatus($id, $estado)
    {
        // OBTER USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('admin') && !$user->can('gerente'))
        {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado das salas.');
            return $this->redirect(['index']);
        }

        // OBTER SALA
        $model = $this->findModel($id);

        // VERIFICAR CINEMA (GERENTE SÓ PODE ALTERAR AS SUAS SALAS)
        if ($user->can('gerente') && !$user->can('admin'))
        {
            $userCinemaId = $user->identity->profile->cinema_id;

            // SE SALA NÃO FOR DO CINEMA DO GERENTE --> MENSAGEM DE ERRO
            if ($model->cinema_id != $userCinemaId)
            {
                Yii::$app->session->setFlash('error', 'Não tem permissão para alterar salas de outro cinema.');
                return $this->redirect(['index']);
            }
        }

        // OBTER ESTADOS VÁLIDOS
        $estadosValidos = array_keys(Sala::optsEstado());

        // SE ESTADO NÃO FOR VÁLIDO --> MENSAGEM DE ERRO
        if (!in_array($estado, $estadosValidos))
        {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO
        if ($model->estado === $estado)
        {
            Yii::$app->session->setFlash('info', "O cinema já se encontra no estado selecionado.");
            return $this->redirect(['index']);
        }

        if ($estado === Sala::ESTADO_ENCERRADA)
        {
            // SE SALA JÁ ESTIVER ENCERRADA --> MENSAGEM DE AVISO
            if ($model->isActivatable())
            {
                Yii::$app->session->setFlash('info', 'Esta sala já se encontra encerrada.');
                return $this->redirect(['index']);
            }

            // SE TIVER SESSÕES ATIVAS --> NÃO DEIXAR ENCERRAR
            if (!$model->isClosable())
            {
                Yii::$app->session->setFlash('error', 'Não é possível encerrar esta sala pois existem sessões ativas ou com bilhetes associadas.');
                return $this->redirect(['index']);
            }
        }

        // ALTERAR ESTADO
        $model->estado = $estado;

        if ($model->save(false, ['estado']))
        {
            $mensagem = $estado === Sala::ESTADO_ATIVA ? 'Sala ativada com sucesso.' : 'Sala encerrada com sucesso.';
            Yii::$app->session->setFlash('success', $mensagem);
        }
        else
        {
            Yii::$app->session->setFlash('error', 'Erro ao alterar o estado da sala.');
        }

        return $this->redirect(['index']);
    }



    /**
     * Finds the Sala model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Sala the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sala::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
