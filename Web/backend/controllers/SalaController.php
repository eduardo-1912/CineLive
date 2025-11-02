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
    public function actionIndex()
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('funcionario')) {
            throw new ForbiddenHttpException('Não tem permissão para aceder a esta página.');
        }

        // CRIAR SEARCH MODEL E QUERY NA DB
        $searchModel = new SalaSearch();
        $params = Yii::$app->request->queryParams;

        // SE FOR ADMIN --> VÊ TODOS OS UTILIZADORES
        if ($user->can('admin')) {
            $dataProvider = $searchModel->search($params);
        }

        // SE FOR GERENTE/FUNCIONÁRIO --> APENAS VÊ OS FUNCIONÁRIOS DO SEU CINEMA
        else {
            $userProfile = $user->identity->profile;

            if (!$userProfile || !$userProfile->cinema_id) {
                throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
            }

            // APLICAR FILTRO DE CINEMA
            $params['SalaSearch']['cinema_id'] = $userProfile->cinema_id;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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

            // SE CINEMA DO USER E CINEMA SALA DIFERENTES --> SEM ACESSO
            if ($cinemaId != $model->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta sala.');
                return $this->redirect('index');
            }

        }

        // SE É ADMIN OU SALA É DO MESMO CINEMA DA SALA --> TEM ACESSO
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

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {

            // FORÇAR CINEMA_ID SE FOR GERENTE
            if ($user->can('gerente')) {
                $model->cinema_id = $user->identity->profile->cinema_id;
            }

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

    public function actionDeactivate($id)
    {
        // OBTER USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('admin') && !$user->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para encerrar salas.');
        }

        // OBTER SALA
        $model = $this->findModel($id);

        // SE FOR GERENTE --> SÓ PODE ENCERRAR SALAS DO SEU CINEMA
        if ($user->can('gerente' && !$user->can('admin'))) {
            $userCinemaId = $user->identity->profile->cinema_id;
            if ($model->cinema_id != $userCinemaId) {
                throw new ForbiddenHttpException('Não tem permissão para encerrar salas de outro cinema.');
            }
        }

        // SE A SALA JÁ ESTÁ ENCERRADA --> VOLTAR
        if ($model->estado === $model::ESTADO_ENCERRADA) {
            Yii::$app->session->setFlash('info', 'Esta sala já se encontra encerrada.');
            return $this->redirect(['index']);
        }

        // ENCERRAR A SALA
        $model->estado = Sala::ESTADO_ENCERRADA;

        // GUARDAR
        if ($model->save(false, ['estado'])) {
            Yii::$app->session->setFlash('success', 'Sala encerrada com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao encerrar a sala.');
        }

        return $this->redirect(['index']);
    }


    public function actionActivate($id)
    {
        // OBTER USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$user->can('admin') && !$user->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para ativar salas.');
        }

        // OBTER SALA
        $model = Sala::findOne($id);

        // SE FOR GERENTE --> SÓ PODE ENCERRAR SALAS DO SEU CINEMA
        if ($user->can('gerente') && !$user->can('admin')) {
            $userCinemaId = $user->identity->profile->cinema_id;
            if ($model->cinema_id != $userCinemaId) {
                throw new ForbiddenHttpException('Não tem permissão para ativar salas de outro cinema.');
            }
        }

        // SE A SALA JÁ ESTÁ ATIVA --> VOLTAR
        if ($model->estado === $model::ESTADO_ATIVA) {
            Yii::$app->session->setFlash('info', 'Esta sala já se encontra ativa.');
            return $this->redirect(['index']);
        }

        // ALTERAR ESTADO
        $model->estado = Sala::ESTADO_ATIVA;

        // GUARDAR
        if ($model->save(false, ['estado'])) {
            Yii::$app->session->setFlash('success', 'Sala ativada com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao ativar a sala.');
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
