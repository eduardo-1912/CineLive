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

    // ADMIN -> VÊ AS SESSÕES DE TODOS OS CINEMAS
    // GERENTE/FUNCIONÁRIO --> APENAS VÊ AS SESSÕES DO SEU CINEMA
    public function actionIndex($cinema_id = null)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // CRIAR SEARCH MODEL E QUERY NA DB
        $searchModel = new SessaoSearch();
        $params = Yii::$app->request->queryParams;

        // SE FOR ADMIN --> VÊ TODOS OS UTILIZADORES
        if ($currentUser->can('admin')) {
            if ($cinema_id !== null) {
                $params['SessaoSearch']['cinema_id'] = $cinema_id;
            }
            $dataProvider = $searchModel->search($params);
        }

        // SE FOR GERENTE/FUNCIONÁRIO --> APENAS VÊ OS FUNCIONÁRIOS DO SEU CINEMA
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


    // ADMIN -> VÊ OS DETALHES DAS SESSÕES DE TODOS OS CINEMAS
    // GERENTE/FUNCIONÁRIO --> APENAS VÊ OS DETALHES DAS SESSÕES DO SEU CINEMA
    public function actionView($id)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // SE FOR GERENTE/FUNCIONÁRIO --> OBTER O SEU CINEMA
        if (!$currentUser->can('admin')) {

            // OBTER ID DO CINEMA DO USER ATUAL
            $cinemaId = $currentUser->identity->profile->cinema_id;

            // OBTER SESSÃO
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

    // ADMIN --> CRIA UMA SESSÃO PARA QUALQUER CINEMA
    // GERENTE --> APENAS CRIA UMA SESSÃO PARA O SEU CINEMA
    public function actionCreate($filme_id = null)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$currentUser->can('gerirSessoes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar sessões.');
            return $this->redirect(['index']);
        }

        // CRIAR NOVA SESSÃO
        $model = new Sessao();

        // VER SE ALGUM FILME FOI PASSADO POR PARÂMETRO
        if ($filme_id !== null) {
            $model->filme_id = $filme_id;
        }

        // SE FOR GERENTE --> FORÇAR ATRIBUIÇÃO CINEMA_ID DO GERENTE
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {
            $model->cinema_id = $currentUser->identity->profile->cinema_id;
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
                    Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar a sessão.');
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    // ADMIN --> EDITA SESSÕES DE QUALQUER CINEMA
    // GERENTE --> APENAS EDITA SESSÕES DO O SEU CINEMA
    public function actionUpdate($id)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$currentUser->can('gerirSessoes')) {
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
                    Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar a sessão.');
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    // ADMIN --> ELIMINA QUALQUER SESSÃO (QUE NÃO TENHA BILHETES ASSOCIADOS)
    // GERENTE --> ELIMNA SESSÕES DO SEU CINEMA (QUE NÃO TENHA BILHETES ASSOCIADOS)
    public function actionDelete($id)
    {
        // OBTER O USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!$currentUser->can('gerirSessoes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar sessões.');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);

        // SE NÃO PODE SER ELIMINADA --> MENSAGEM DE ERRO
        if (!$model->isDeletable()) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar sessões a decorrer ou com bilhetes associados.');
            return $this->redirect(['index']);
        }

        // SE FOR GERENTE --> SÓ PODE ELIMINAR SESSÕES DO SEU CINEMA
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {

            // OBTER CINEMA DO GERENTE
            $userCinemaId = $currentUser->identity->profile->cinema_id;

            // SE CINEMAS NÃO COINCIDIREM --> SEM PERMISSÃO
            if ($model->cinema_id != $userCinemaId) {
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


    protected function findModel($id)
    {
        if (($model = Sessao::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
