<?php

namespace backend\controllers;

use common\models\Cinema;
use common\models\Filme;
use Yii;
use common\models\Sessao;
use backend\models\SessaoSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class SessaoController extends Controller
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
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($cinema_id = null)
    {
        $currentUser = Yii::$app->user;
        $userCinemaId = $currentUser->identity->profile->cinema_id ?: null;
        $cinema = Cinema::findOne($userCinemaId ?? $cinema_id);

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $cinema]);
        $verSessoesCinema = $currentUser->can('verSessoesCinema', ['model' => $cinema]);

        $searchModel = new SessaoSearch();
        $params = Yii::$app->request->queryParams;

        if ($gerirSessoes) {
            if ($cinema_id) {
                $params['SessaoSearch']['cinema_id'] = $cinema_id;
            }
        }
        elseif (($gerirSessoesCinema || $verSessoesCinema) && $userCinemaId) {
            $params['SessaoSearch']['cinema_id'] = $userCinemaId;
        }
        else {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver sessões.');
            return $this->goHome();
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'cinema' => $cinema,
            'gerirSessoes' => $gerirSessoes || $gerirSessoesCinema,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cinemaOptions' => Cinema::findAllList(),
            'estadoOptions' => Sessao::optsEstado(),
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $model->cinema]);
        $verSessoesCinema = $currentUser->can('verSessoesCinema', ['model' => $model->cinema]);

        if (!$gerirSessoes && !$gerirSessoesCinema && !$verSessoesCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta sessão.');
            return $this->redirect('index');
        }

        $comprasDataProvider = new ActiveDataProvider([
            'query' => $model->getCompras(),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize']],
            'sort' => ['defaultOrder' => ['data' => SORT_DESC]],
        ]);

        $mapaLugares = [];
        for ($fila = 1; $fila <= $model->sala->num_filas; $fila++) {
            for ($coluna = 1; $coluna <= $model->sala->num_colunas; $coluna++) {

                $lugar = chr(64 + $fila) . $coluna;

                $mapaLugares[$fila][$coluna] = [
                    'label' => $lugar,
                    'ocupado' => in_array($lugar, $model->lugaresOcupados),
                    'confirmado' => in_array($lugar, $model->lugaresConfirmados),
                    'compraId' => $model->getCompraIdPorLugar($lugar),
                ];
            }
        }

        return $this->render('view', [
            'model' => $model,
            'gerirSessoes' => $gerirSessoes,
            'gerirSessoesCinema' => $gerirSessoesCinema,
            'mapaLugares' => $mapaLugares,
            'comprasDataProvider' => $comprasDataProvider,
        ]);
    }

    public function actionCreate($cinema_id = null, $data = null, $filme_id = null, $hora_inicio = null)
    {
        $currentUser = Yii::$app->user;
        $userCinema = $currentUser->identity->profile->cinema ?? null;

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $userCinema]);

        if (!$gerirSessoes && (!$gerirSessoesCinema && !$userCinema)) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar sessões.');
            return $this->redirect(['index']);
        }

        $model = new Sessao();

        // Se é gerente --> forçar cinema dele
        if ($gerirSessoesCinema) {
            $model->cinema_id = $userCinema->id;
            $cinema_id = $model->cinema_id;
        }

        if ($gerirSessoes && $cinema_id) {
            $cinema = Cinema::findOne($cinema_id);

            if ($cinema && $cinema->isEstadoEncerrado()) {
                Yii::$app->session->setFlash('error', 'Não é possível criar sessões para um cinema encerrado.');
                return $this->redirect(['create']);
            }

            $model->cinema_id = $cinema_id;
        }

        $model->filme_id = $filme_id;
        $model->data = $data ?? date('Y-m-d');
        $model->hora_inicio = $hora_inicio;

        // Calcular a hora de fim
        if ($model->filme_id && $model->hora_inicio) {
            $filme = Filme::findOne($model->filme_id);
            if ($filme) {
                $model->hora_fim = $model->getHoraFimCalculada($filme->duracao);
            }
        }

        $cinemaOptions = Cinema::findAtivosList();
        $filmeOptions = Filme::findPorEstadoList(Filme::ESTADO_EM_EXIBICAO);

        // Salas disponíveis
        $salaOptions = [];
        if ($model->cinema_id && $model->data && $model->hora_inicio && $model->hora_fim) {
            $cinema = Cinema::findOne($model->cinema_id);
            $salas = $cinema->getSalasDisponiveis($model->data, $model->hora_inicio, $model->hora_fim);
            $salaOptions = ArrayHelper::map($salas, 'id', 'nome');
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->sala_id && $model->data && $model->hora_inicio && $model->hora_fim && $model->filme_id && $model->cinema_id) {

                if ($model->validateHorario()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Sessão criada com sucesso.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                    else {
                        Yii::$app->session->setFlash('error', 'Erro ao criar a sessão.');
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'gerirSessoes' => $gerirSessoes,
            'cinemaOptions' => $cinemaOptions,
            'filmeOptions' => $filmeOptions,
            'salaOptions' => $salaOptions,
        ]);
    }

    public function actionUpdate($id, $data = null, $filme_id = null, $hora_inicio = null)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $model->cinema]);

        if ((!$gerirSessoes && !$gerirSessoesCinema) || !$model->isEditable()) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar esta sessão.');
            return $this->redirect(['index']);
        }

        $hasBilhetes = !$model->isNewRecord && count($model->lugaresOcupados) > 0;

        $model->filme_id = $filme_id ?? $model->filme_id;
        $model->data = $data ?? $model->data;
        $model->hora_inicio = $hora_inicio ?? $model->hora_inicio;

        // Guardar dados anteriores
        $anterior = $model;

        // Calcular a hora de fim
        if ($model->filme_id && $model->hora_inicio) {
            $filme = Filme::findOne($model->filme_id);
            if ($filme) {
                $model->hora_fim = $model->getHoraFimCalculada($filme->duracao);
            }
        }

        $cinemaOptions = Cinema::findAtivosList();
        $filmeOptions = Filme::findPorEstadoList(Filme::ESTADO_EM_EXIBICAO);

        // Salas disponíveis
        $salaOptions = [];
        if ($model->data && $model->hora_inicio && $model->hora_fim) {
            $cinema = Cinema::findOne($model->cinema_id);
            $salas = $cinema->getSalasDisponiveis($model->data, $model->hora_inicio, $model->hora_fim, $model->sala_id);
            $salaOptions = ArrayHelper::map($salas, 'id', 'nome');
        }

        if ($model->load(Yii::$app->request->post())) {
            // Não deixar alterar o cinema
            $model->cinema_id = $anterior->cinema_id;

            // Se tiver bilhetes associados --> apenas deixar alterar sala
            if (count($model->lugaresOcupados) > 0) {
                $model->updateAttributes(['sala_id' => $model->sala_id]);
            }

            if ($model->validateHorario() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Sessão atualizada com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar a sessão.');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'gerirSessoes' => $gerirSessoes,
            'cinemaOptions' => $cinemaOptions,
            'filmeOptions' => $filmeOptions,
            'salaOptions' => $salaOptions,
            'hasBilhetes' => $hasBilhetes,
        ]);
    }

    public function actionDelete($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $model->cinema]);

        if ((!$gerirSessoes && !$gerirSessoesCinema) || !$model->isDeletable()) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar esta sessão.');
            return $this->redirect('index');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Sessão eliminada com sucesso.');
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao eliminar a sessão.');
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
