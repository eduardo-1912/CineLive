<?php

namespace backend\controllers;

use common\models\Sala;
use common\models\User;
use common\models\UserProfile;
use Yii;
use common\models\Cinema;
use backend\models\CinemaSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class CinemaController extends Controller
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
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        if (!Yii::$app->user->can('gerirCinemas')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para gerir cinemas.');
            return $this->goHome();
        }

        $searchModel = new CinemaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'estadoFilterOptions' => Cinema::optsEstado(),
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $userCinemaId = $currentUser->identity->profile->cinema->id ?? null;
        $model = $this->findModel($id);

        $gerirCinemas = $currentUser->can('gerirCinemas');
        $verCinema = $currentUser->can('verCinema', ['model' => $model]);

        if (!$gerirCinemas && !$verCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver este cinema.');
            return $userCinemaId ? $this->redirect(['cinema/view', 'id' => $userCinemaId]) : $this->goHome();
        }

        $editarCinema = $currentUser->can('editarCinema', ['model' => $model]);
        $gerirSalas = $currentUser->can('gerirSalas')
            || $currentUser->can('gerirSalasCinema', ['model' => $model]);

        $salasDataProvider = new ActiveDataProvider([
            'query' => $model->getSalas()->orderBy(['numero' => SORT_ASC]),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize']],
        ]);

        return $this->render('view', [
            'model' => $model,
            'currentUser' => $currentUser,
            'gerirCinemas' => $gerirCinemas,
            'verCinema' => $verCinema,
            'editarCinema' => $editarCinema,
            'gerirSalas' => $gerirSalas,
            'salasDataProvider' => $salasDataProvider,
        ]);
    }

    public function actionCreate()
    {
        $currentUser = Yii::$app->user;
        $gerirCinemas = $currentUser->can('gerirCinemas');

        if (!$gerirCinemas) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar cinemas.');
            return $this->goHome();
        }

        $model = new Cinema();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Cinema criado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao criar o cinema.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'gerirCinemas' => $gerirCinemas,
            'estadoOptions' => $model::optsEstado(),
        ]);
    }

    public function actionUpdate($id)
    {
        $currentUser = Yii::$app->user;
        $userCinemaId = $currentUser->identity->profile->cinema->id ?? null;
        $model = $this->findModel($id);

        $gerirCinemas = $currentUser->can('gerirCinemas');
        $editarCinema = $currentUser->can('editarCinema', ['model' => $model]);

        if (!$gerirCinemas && !$editarCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar este cinema.');
            return $userCinemaId ? $this->redirect(['cinema/update', 'id' => $userCinemaId]) : $this->goHome();
        }

        $estadoAntigo = $model->estado;

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->validateHorario()) {
                Yii::$app->session->setFlash('error', 'Existem sessões ou alugueres futuros fora do novo horário.');
                return $this->redirect(['update', 'id' => $model->id]);
            }

            if ($model->estado !== $estadoAntigo) {
                if ($model->isEstadoAtivo() && !$model->isClosable()) {
                    Yii::$app->session->setFlash('error', 'Não é possível encerrar o cinema pois existem sessões ou alugueres ativos.');

                    $model->estado = $estadoAntigo;
                    $model->save(['estado']);

                    return $this->redirect(['update', 'id' => $model->id]);
                }

                $this->atualizarStaffSalas($model);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Cinema atualizado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'gerirCinemas' => $gerirCinemas,
            'estadoOptions' => $model::optsEstado(),
        ]);
    }

    public function actionChangeStatus($id, $estado)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('gerirCinemas')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado de cinemas.');
            return $this->goHome();
        }

        if ($model->estado === $estado) {
            return $this->redirect(['index']);
        }

        if ($estado === $model::ESTADO_ENCERRADO && !$model->isClosable()) {
            Yii::$app->session->setFlash('error', 'Não é possível encerrar o cinema pois existem sessões ou alugueres ativos.');
            return $this->redirect(['index']);
        }

        // Alterar o estado
        $model->estado = $estado;

        if ($model->save(['estado'])) {
            $this->atualizarStaffSalas($model);
            $msg = $model->isEstadoAtivo() ? 'Cinema ativado com sucesso.' : 'Cinema encerrado com sucesso.';
            Yii::$app->session->setFlash('success', $msg);
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao alterar o estado do cinema.');
        }

        return $this->redirect(['index']);
    }

    private function atualizarStaffSalas(Cinema $cinema)
    {
        $ativo = ($cinema->estado === $cinema::ESTADO_ATIVO);

        User::updateAll(
            ['status' => $ativo ? User::STATUS_ACTIVE : User::STATUS_INACTIVE],
            ['id' => UserProfile::find()->select('user_id')->where(['cinema_id' => $cinema->id])]
        );

        Sala::updateAll(
            ['estado' => $ativo ? Sala::ESTADO_ATIVA : Sala::ESTADO_ENCERRADA],
            ['cinema_id' => $cinema->id]
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
