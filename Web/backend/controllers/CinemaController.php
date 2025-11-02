<?php

namespace backend\controllers;

use common\models\User;
use common\models\UserProfile;
use Yii;
use common\models\Cinema;
use backend\models\CinemaSearch;
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

    /**
     * Lists all Cinema models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CinemaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Cinema model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // VER DETALHES DE UM CINEMA (ADMIN VÊ TODOS, GERENTE/FUNCIONÁRIO APENAS O SEU)
    public function actionView($id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;
        $user = $currentUser->identity;
        $userCinemaId = $user->profile->cinema_id ?? null;

        // ADMIN --> PODE VER QUALQUER CINEMA
        if ($currentUser->can('admin')) {
            $model = $this->findModel($id);
            return $this->render('view', ['model' => $model]);
        }

        // GERENTE ou FUNCIONÁRIO --> APENAS VÊ O SEU CINEMA
        if ($currentUser->can('gerente') || $currentUser->can('funcionario')) {

            // SE NENHUM ID FOR PASSADO --> REDIRECIONAR PARA O CINEMA DO USER ATUAL
            if ($id === null && $userCinemaId) {
                return $this->redirect(['view', 'id' => $userCinemaId]);
            }

            // SE O ID PASSADO FOR IGUAL AO DO USER ATUAL --> PERMITIR
            if ($id == $userCinemaId) {
                $model = $this->findModel($userCinemaId);
                return $this->render('view', ['model' => $model]);
            }

            // SE TENTAR VER OUTRO CINEMA --> REDIRECIONAR PARA O CINEMA DELE
            return $this->redirect(['view', 'id' => $userCinemaId]);
        }

        // CASO CONTRÁRIO --> SEM PERMISSÃO
        throw new ForbiddenHttpException('Não tem permissão para ver este cinema.');
    }


    /**
     * Creates a new Cinema model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Cinema();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Cinema model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // EDITAR CINEMA (ADMIN PODE EDITAR TODOS, GERENTE APENAS O SEU)
    public function actionUpdate($id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;
        $user = $currentUser->identity;
        $userCinemaId = $user->profile->cinema_id ?? null;

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

        // CASO CONTRÁRIO --> SEM PERMISSÃO
        else {
            throw new ForbiddenHttpException('Não tem permissão para editar este cinema.');
        }

        // GUARDAR O ESTADO ANTERIOR DO CINEMA
        $estadoAntigo = $model->estado;


        // ATUALIZAÇÃO DO CINEMA
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            // SE O ESTADO MUDOU --> ATUALIZAR O STAFF
            if ($estadoAntigo !== $model->estado) {
                $this->atualizarEstadoUtilizadores($model);

                if ($model->estado === Cinema::ESTADO_ATIVO) {
                    Yii::$app->session->setFlash('success', 'Cinema reativado. Gerente e funcionários inativos voltaram a estar ativos.');
                }
                else {
                    Yii::$app->session->setFlash('warning', 'Cinema encerrado. Gerente e funcionários foram desativados.');
                }
            }
            else {
                Yii::$app->session->setFlash('success', 'Cinema atualizado com sucesso.');
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    // ENCERRAR CINEMA (APENAS ADMIN)
    public function actionDeactivate($id)
    {
        $model = $this->findModel($id);

        // SE NÃO É ADMIN --> SEM PERMISSÃO
        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Não tem permissão para encerrar cinemas.');
        }

        // SE O CINEMA JÁ ESTÁ ENCERRADO --> VOLTAR
        if ($model->estado === Cinema::ESTADO_ENCERRADO) {
            Yii::$app->session->setFlash('info', 'Este cinema já se encontra encerrado.');
            return $this->redirect(['index']);
        }

        // ENCERRAR O CINEMA
        $model->estado = Cinema::ESTADO_ENCERRADO;
        $model->save(false, ['estado']);

        // DESATIVAR GERENTE E FUNCIONÁRIOS
        $this->atualizarEstadoUtilizadores($model);

        Yii::$app->session->setFlash('success', 'Cinema encerrado. Gerente e funcionários foram desativados.');
        return $this->redirect(['index']);
    }

    // ATIVAR CINEMA (APENAS ADMIN)
    public function actionActivate($id)
    {
        $model = $this->findModel($id);

        // SE NÃO É ADMIN --> SEM PERMISSÃO
        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Não tem permissão para ativar cinemas.');
        }

        // SE O CINEMA JÁ ESTÁ ENCERRADO --> VOLTAR
        if ($model->estado === Cinema::ESTADO_ATIVO) {
            Yii::$app->session->setFlash('info', 'Este cinema já se encontra ativo.');
            return $this->redirect(['index']);
        }

        // ENCERRAR O CINEMA
        $model->estado = Cinema::ESTADO_ATIVO;
        $model->save(false, ['estado']);

        // REATIVAR GERENTE E FUNCIONÁRIOS
        $this->atualizarEstadoUtilizadores($model);

        Yii::$app->session->setFlash('success', 'Cinema reativado. Gerente e funcionários inativos voltaram a estar ativos.');
        return $this->redirect(['index']);
    }

    // ATIVAR/DESATIVAR STAFF CONSOANTE O ESTADO DO CINEMA
    private function atualizarEstadoUtilizadores(Cinema $cinema)
    {
        // GERENTE
        if ($cinema->gerente_id) {
            $gerente = User::findOne($cinema->gerente_id);
            if ($gerente) {
                $gerente->status = ($cinema->estado === Cinema::ESTADO_ATIVO) ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;
                $gerente->save(false, ['status']);
            }
        }

        // FUNCIONÁRIOS
        User::updateAll(
            ['status' => ($cinema->estado === Cinema::ESTADO_ATIVO) ? User::STATUS_ACTIVE : User::STATUS_INACTIVE],
            ['id' => UserProfile::find()->select('user_id')->where(['cinema_id' => $cinema->id])]
        );
    }

    /**
     * Finds the Cinema model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Cinema the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Cinema::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
