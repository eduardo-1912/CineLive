<?php

namespace backend\controllers;

use common\models\Cinema;
use Yii;
use common\models\Sala;
use backend\models\SalaSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class SalaController extends Controller
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
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $currentUser = Yii::$app->user;
        $cinema = Cinema::findOne($currentUser->identity->profile->cinema->id ?? null);

        $searchModel = new SalaSearch();
        $params = Yii::$app->request->queryParams;

        $gerirSalas = $currentUser->can('gerirSalas');
        $gerirSalasCinema = $currentUser->can('gerirSalasCinema', ['model' => $cinema]);
        $verSalasCinema = $currentUser->can('verSalasCinema', ['model' => $cinema]);

        if (!$gerirSalas && !$gerirSalasCinema && !$verSalasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver salas.');
            return $this->redirect('index');
        }

        if ($cinema && ($gerirSalasCinema || $verSalasCinema)) {
            $params['SalaSearch']['cinema_id'] = $cinema->id;
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'cinema' => $cinema,
            'gerirSalas' => $gerirSalas || $gerirSalasCinema,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cinemaOptions' => Cinema::findAllList(),
            'estadoOptions' => Sala::optsEstado(),
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $userCinemaId = $currentUser->identity->profile->cinema->id ?? null;
        $model = $this->findModel($id);

        $gerirSalas = $currentUser->can('gerirSalas');
        $gerirSalasCinema = $currentUser->can('gerirSalasCinema', ['model' => $model->cinema]);
        $verSalasCinema = $currentUser->can('verSalasCinema', ['model' => $model->cinema]);

        if (!$gerirSalas && !$gerirSalasCinema && !$verSalasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta sala.');
            return $this->redirect('index');
        }

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $model->cinema]);

        $sessoesDataProvider = new ActiveDataProvider([
            'query' => $model->getSessoes(),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize']],
            'sort' => ['defaultOrder' => ['data' => SORT_DESC]],
        ]);

        return $this->render('view', [
            'model' => $model,
            'gerirSalas' => $gerirSalas || $gerirSalasCinema,
            'gerirSessoes' => $gerirSessoes || $gerirSessoesCinema,
            'sessoesDataProvider' => $sessoesDataProvider,
        ]);
    }

    public function actionCreate($cinema_id = null)
    {
        $currentUser = Yii::$app->user;
        $userCinema = $currentUser->identity->profile->cinema ?? null;

        $gerirSalas = $currentUser->can('gerirSalas');
        $gerirSalasCinema = $currentUser->can('gerirSalasCinema', ['model' => $userCinema]);

        if (!$gerirSalas && !$gerirSalasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar salas.');
            return $this->redirect('index');
        }

        // Se é gerente --> forçar o seu cinema
        if ($gerirSalasCinema && $userCinema) $cinema_id = $userCinema->id;

        // Obter cinema e próximo número da sala
        $cinema = Cinema::findOne($cinema_id);
        if ($cinema) $proximoNumero = $cinema->getProximoNumeroSala();

        if ($gerirSalas && $cinema && $cinema->isEstadoEncerrado()) {
            Yii::$app->session->setFlash('error', 'Não é possível criar salas para um cinema encerrado.');
            return $this->redirect(['create']);
        }

        $model = new Sala();
        $model->cinema_id = $cinema_id;

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
            'proximoNumero' => $proximoNumero ?? null,
            'gerirSalas' => $gerirSalas,
            'gerirSalasCinema' => $gerirSalasCinema,
            'userCinema' => $userCinema ?? null,
            'cinemaOptions' => Cinema::findAtivosList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $currentUser = Yii::$app->user;
        $userCinema = $currentUser->identity->profile->cinema ?? null;
        $model = $this->findModel($id);

        $gerirSalas = $currentUser->can('gerirSalas');
        $gerirSalasCinema = $currentUser->can('gerirSalasCinema', ['model' => $model->cinema]);

        if (!$gerirSalas && !$gerirSalasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar esta sala.');
            return $this->redirect('index');
        }

        if ($model->cinema->isEstadoEncerrado()) {
            Yii::$app->session->setFlash('error', 'Não pode editar a sala de um cinema encerrado.');
            return $this->redirect('index');
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($gerirSalasCinema) {
                $model->cinema_id = $userCinema->id;
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
            'gerirSalas' => $gerirSalas,
            'gerirSalasCinema' => $gerirSalasCinema,
            'userCinema' => $userCinema ?? null,
            'cinemaOptions' => Cinema::findAtivosList(),
        ]);
    }

    public function actionChangeStatus($id, $estado)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirSalas = $currentUser->can('gerirSalas');
        $gerirSalasCinema = $currentUser->can('gerirSalasCinema', ['model' => $model->cinema]);

        if (!$gerirSalas && !$gerirSalasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado desta sala.');
            return $this->redirect('index');
        }

        if ($model->cinema->isEstadoEncerrado()) {
            Yii::$app->session->setFlash('error', 'Não pode alterar o estado da sala de um cinema encerrado.');
            return $this->redirect('index');
        }

        if ($model->estado === $estado) {
            return $this->redirect(['index']);
        }

        if ($estado === $model::ESTADO_ENCERRADA && !$model->isClosable()) {
            Yii::$app->session->setFlash('error', 'Não é possível encerrar a sala pois existem sessões ou alugueres ativos.');
            return $this->redirect(['index']);
        }

        // Alterar o estado
        $model->estado = $estado;

        if ($model->save(['estado'])) {
            $msg = $model->isEstadoAtiva() ? 'Sala ativada com sucesso.' : 'Sala encerrada com sucesso.';
            Yii::$app->session->setFlash('success', $msg);
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
