<?php

namespace backend\controllers;

use DateTime;
use Yii;
use common\models\Sessao;
use backend\models\SessaoSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SessaoController implements the CRUD actions for Sessao model.
 */
class SessaoController extends Controller
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
                        'actions' => ['create', 'update', 'delete']
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
     * Lists all Sessao models.
     * @return mixed
     */
    public function actionIndex($cinema_id = null)
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$user->can('funcionario')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para aceder a esta página.');
            return $this->redirect(['index']);
        }

        // CRIAR SEARCH MODEL E QUERY NA DB
        $searchModel = new SessaoSearch();
        $params = Yii::$app->request->queryParams;

        // SE FOR ADMIN --> VÊ TODOS OS UTILIZADORES
        if ($user->can('admin')) {
            if ($cinema_id !== null) {
                $params['SessaoSearch']['cinema_id'] = $cinema_id;
            }
            $dataProvider = $searchModel->search($params);
        }

        // SE FOR GERENTE/FUNCIONÁRIO --> APENAS VÊ OS FUNCIONÁRIOS DO SEU CINEMA
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
            $params['SessaoSearch']['cinema_id'] = $userProfile->cinema_id;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cinemaId' => $cinema_id,
        ]);
    }

    /**
     * Displays a single Sessao model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$user->can('funcionario')){
            Yii::$app->session->setFlash('error', 'Não tem permissão para aceder a esta página.');
            return $this->redirect(['index']);
        }

        if (!$user->can('admin')) {
            // OBTER ID DO CINEMA DO USER ATUAL
            $cinemaId = $user->identity->profile->cinema_id;

            $model = $this->findModel($id);

            // SE CINEMA DO USER E CINEMA DA SESSÃO FOREM DIFERENTES --> SEM ACESSO
            if ($cinemaId != $model->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta sessão.');
                return $this->redirect('index');
            }
        }

        // SE É ADMIN OU UTILIZADOR É DO MESMO CINEMA DA SESSÃO --> TEM ACESSO
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Sessao model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($filme_id = null)
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$user->can('gerirSessoes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar sessões.');
            return $this->redirect(['index']);
        }

        // CRIAR NOVA SESSÃO
        $model = new Sessao();

        if ($filme_id !== null) {
            $model->filme_id = $filme_id;
        }

        // SE FOR GERENTE --> FORÇAR ATRIBUIÇÃO CINEMA_ID DO GERENTE
        if ($user->can('gerente') && !$user->can('admin')) {
            $model->cinema_id = $user->identity->profile->cinema_id;
        }

        // METER A DATA DE HOJE POR DEFAULT
        if ($model->isNewRecord) {
            $model->data = date('Y-m-d');
        }

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {
            if ($model->sala_id && $model->data && $model->hora_inicio && $model->hora_fim && $model->filme_id && $model->cinema_id) {

                // VALIDAR HORÁRIO
                if (!$model->validateHorario()) {
                    return $this->render('create', ['model' => $model]);
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Sessão criada com sucesso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                else {
                    Yii::$app->session->setFlash('error', 'Erro ao criar a sessão.');
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Sessao model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // OBTER O USER ATUAL
        $user = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$user->can('gerirSessoes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar sessões.');
            return $this->redirect(['index']);
        }

        // OBTER A SESSÃO
        $model = $this->findModel($id);

        // BLOQUEAR EDIÇÃO SE ESTIVER A DECORRER
        if (!$model->isEditable()) {
            Yii::$app->session->setFlash('error', 'Não é possível editar sessões que estejam a decorrer.');
            return $this->redirect(['index']);
        }

        // GUARDAR
        if ($model->load(Yii::$app->request->post())) {
            if ($model->sala_id && $model->data && $model->hora_inicio && $model->hora_fim && $model->filme_id && $model->cinema_id) {

                // SE TIVER BILHETES ASSOCIADOS --> APENAS DEIXA EDITAR SALA
                if (count($model->lugaresOcupados) > 0) {
                    $model->updateAttributes(['sala_id' => $model->sala_id]);

                    Yii::$app->session->setFlash('success', 'Sessão atualizada com sucesso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }

                // VALIDAR HORÁRIO
                if (!$model->validateHorario()) {
                    return $this->render('update', ['model' => $model]);
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Sessão atualizada com sucesso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                else {
                    Yii::$app->session->setFlash('error', 'Erro ao atualizar a sessão.');
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Sessao model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$currentUser->can('gerirSessoes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar sessões.');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);

        if (!$model->isDeletable()) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar sessões a decorrer ou com bilhetes associados.');
            return $this->redirect(['index']);
        }

        // SE FOR GERENTE --> SÓ PODE ELIMINAR SESSÕES DO SEU CINEMA
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {

            // OBTER CINEMA DO GERENTE
            $currentUserCinemaId = $currentUser->identity->profile->cinema_id;

            // SE CINEMAS NÃO COINCIDIREM --> SEM PERMISSÃO
            if ($model->cinema_id != $currentUserCinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar sessões de outro cinema.');
                return $this->redirect(['index']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Sessão eliminada com sucesso.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar a sessão.');
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Sessao model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Sessao the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sessao::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
