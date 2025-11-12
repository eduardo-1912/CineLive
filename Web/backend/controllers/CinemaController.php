<?php

namespace backend\controllers;

use common\models\User;
use common\models\UserProfile;
use Yii;
use common\models\Cinema;
use backend\models\CinemaSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CinemaController implements the CRUD actions for Cinema model.
 */
class CinemaController extends Controller
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
                        'actions' => ['update']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['view']
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

    // ADMIN --> VÊ TODOS OS CINEMAS
    public function actionIndex()
    {
        $searchModel = new CinemaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'estadoFilterOptions' => Cinema::optsEstado()
        ]);
    }


    // ADMIN --> VÊ DETALHES DE TODOS OS CINEMAS
    // GERENTE/FUNCIONÁRIO --> APENAS VÊ O SEU CINEMA
    public function actionView($id = null)
    {
        $currentUser = Yii::$app->user;
        $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

        // SE GERENTE OU FUNCIONÁRIO SEM ID --> REDIRECIONA PARA O SEU CINEMA
        if (($currentUser->can('gerente') || $currentUser->can('funcionario')) && $id === null && $userCinemaId) {
            return $this->redirect(['view', 'id' => $userCinemaId]);
        }

        // OBTER CINEMA
        $model = $this->findModel($id);

        // PERMISSÕES
        if (!($currentUser->can('admin') || $currentUser->can('gerirCinemas'))) {
            if ($currentUser->can('gerente') || $currentUser->can('funcionario')) {
                if ($model->id != $userCinemaId) {
                    return $this->redirect(['view', 'id' => $userCinemaId]);
                }
            } else {
                throw new ForbiddenHttpException('Não tem permissão para ver este cinema.');
            }
        }

        // PROVIDER DAS SALAS
        $salasDataProvider = new ActiveDataProvider([
            'query' => $model->getSalas()->orderBy(['numero' => SORT_ASC]),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize']],
        ]);

        return $this->render('view', [
            'model' => $model,
            'salasDataProvider' => $salasDataProvider,
        ]);
    }


    // ADMIN --> CRIA CINEMA
    public function actionCreate()
    {
        $model = new Cinema();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Cinema criado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar o cinema.');
            }
        }

        $dropdownEstados = $model::optsEstado();

        return $this->render('create', [
            'model' => $model,
            'dropdownEstados' => $dropdownEstados,
        ]);
    }


    // ADMIN --> EDITA QUALQUER CINEMA
    // GERENTE --> APENAS EDITA O SEU
    public function actionUpdate($id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;
        $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

        // VERIFICAR PERMISSÕES
        if (!$currentUser->can('gerirCinemas')) {
            throw new ForbiddenHttpException('Não tem permissão para editar cinemas.');
        }

        // ADMIN --> PODE EDITAR QUALQUER CINEMA
        if ($currentUser->can('admin')) {
            $model = $this->findModel($id);
        }

        // GERENTE --> SÓ PODE EDITAR O SEU CINEMA
        elseif ($currentUser->can('gerente')) {

            // SE NENHUM ID FOR PASSADO --> REDIRECIONAR PARA O CINEMA DELE
            if ($id === null && $userCinemaId) {
                return $this->redirect(['update', 'id' => $userCinemaId]);
            }

            // SE TENTAR ACEDER A OUTRO CINEMA --> REDIRECIONAR PARA O CINEMA DELE
            if ($id != $userCinemaId) {
                return $this->redirect(['update', 'id' => $userCinemaId]);
            }

            // CASO VÁLIDO --> CARREGAR MODELO
            $model = $this->findModel($userCinemaId);
        }

        // GUARDAR O ESTADO ANTERIOR DO CINEMA
        $estadoAntigo = $model->estado;


        // ATUALIZAÇÃO DO CINEMA
        if ($model->load(Yii::$app->request->post())) {

            // VERIFICAR SE TEM CONFILTOS COM O HORÁRIO
            if ($model->hasConflitosHorario()) {
                Yii::$app->session->setFlash('error', 'Existem sessões ou alugueres futuros fora do novo horário.');
                return $this->redirect(['update', 'id' => $model->id]);
            }

            if ($model->save()) {
                // SE O ESTADO MUDOU --> ATUALIZAR O STAFF
                if ($estadoAntigo !== $model->estado) {
                    if ($model->isEstadoEncerrado()) {

                        // SE NÃO PODE SER ENCERRADO --> REPOR ESTADO ANTERIOR
                        if ($model->hasSessoesAtivas() || $model->hasAlugueresAtivos()) {
                            Yii::$app->session->setFlash('error', 'Não é possível encerrar este cinema pois existem sessões ou alugueres ativos ou pendentes.');

                            // REPOR ESTADO ANTERIOR
                            $model->estado = $estadoAntigo;
                            $model->save(false, ['estado']);

                            return $this->redirect(['update', 'id' => $model->id]);
                        }

                        // DESATIVAR UTILIZADORES
                        $this->atualizarEstadoUtilizadores($model);
                        Yii::$app->session->setFlash('success', 'Cinema encerrado. Gerente e funcionários foram desativados.');
                    }
                    elseif ($model->isEstadoAtivo()) {
                        // ATIVAR UTILIZADORES
                        $this->atualizarEstadoUtilizadores($model);
                        Yii::$app->session->setFlash('success', 'Cinema reativado com sucesso. Gerente e funcionários voltaram a estar ativos.');
                    }

                }
                else {
                    Yii::$app->session->setFlash('success', 'Cinema atualizado com sucesso.');
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    // ADMIN --> MUDA O ESTADO DO CINEMA
    public function actionChangeStatus($id, $estado)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$currentUser->can('gerirCinemas')) {
            throw new ForbiddenHttpException('Não tem permissão para alterar o estado dos cinemas.');
        }

        // OBTER O CINEMA A SER ALTERADO
        $model = $this->findModel($id);

        // VERIFICAR SE O ESTADO É VÁLIDO
        $estadosValidos = array_keys(Cinema::optsEstado());
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', "O cinema já se encontra no estado selecionado.");
            return $this->redirect(['index']);
        }

        // SE TENTAR ENCERRAR
        if ($estado === Cinema::ESTADO_ENCERRADO) {

            // SE NÃO PODE SER ENCERRADO --> MENSAGEM DE ERRO
            if (!$model->isClosable()) {
                Yii::$app->session->setFlash('error', 'Não é possível encerrar este cinema pois existem sessões ou alugueres ativos ou pendentes.');
                return $this->redirect(['index']);
            }

            // ALTERAR ESTADO PARA ENCERRADO
            $model->estado = Cinema::ESTADO_ENCERRADO;

            // DESATIVAR GERENTE E FUNCIONÁRIOS
            if ($model->save(false, ['estado'])) {
                $this->atualizarEstadoUtilizadores($model);
                Yii::$app->session->setFlash('success', 'Cinema encerrado. Gerente e funcionários foram desativados.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao encerrar o cinema.');
            }
        }

        // SE TENTAR ATIVAR
        if ($estado === Cinema::ESTADO_ATIVO) {

            // ALTERAR ESTADO PARA ATIVO
            $model->estado = Cinema::ESTADO_ATIVO;

            // REATIVAR GERENTE E FUNCIONÁRIOS
            if ($model->save(false, ['estado'])) {
                $this->atualizarEstadoUtilizadores($model);
                Yii::$app->session->setFlash('success', 'Cinema reativado. Gerente e funcionários voltaram a estar ativos.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao ativar o cinema.');
            }
        }

        return $this->redirect(['index']);
    }


    // ATIVAR/DESATIVAR STAFF CONSOANTE O ESTADO DO CINEMA
    private function atualizarEstadoUtilizadores(Cinema $cinema)
    {
        User::updateAll(
            ['status' => ($cinema->estado === Cinema::ESTADO_ATIVO) ? User::STATUS_ACTIVE : User::STATUS_INACTIVE],
            ['id' => UserProfile::find()->select('user_id')->where(['cinema_id' => $cinema->id])]
        );
    }


    protected function findModel($id)
    {
        if (($model = Cinema::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
