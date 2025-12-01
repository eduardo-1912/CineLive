<?php

namespace frontend\controllers;

use common\models\Cinema;
use common\models\Sala;
use Yii;
use common\models\AluguerSala;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


class AluguerSalaController extends Controller
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
                        'roles' => ['cliente'],
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
        $alugueres = Yii::$app->user->identity->getAluguerSalas()->orderBy(['id' => SORT_DESC])->all();

        return $this->render('index', [
            'alugueres' => $alugueres,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('verAlugueres', ['model' => $model])) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver este pedido de aluguer.');
            return $this->redirect(['aluguer-sala/index']);
        }

        return $this->render('view', [
            'model' => $model
        ]);
    }

    public function actionCreate($cinema_id = null, $data = null, $hora_inicio = null, $hora_fim = null)
    {
        if (!Yii::$app->user->can('criarAluguer')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar um pedido de aluguer.');
            return $this->goHome();
        }

        $model = new AluguerSala();

        // Preencher dados do Form GET
        $model->cinema_id = $cinema_id ?? Yii::$app->request->cookies->getValue('cinema_id', null);
        $model->data = $data ?? date('Y-m-d');
        $model->hora_inicio = $hora_inicio;
        $model->hora_fim = $hora_fim;

        $cinemaOptions = Cinema::findAtivosList();

        $salaOptions = [];
        if ($cinema_id && $data && $hora_inicio && $hora_fim) {
            $cinema = Cinema::findOne($cinema_id);
            $salas = $cinema->getSalasDisponiveis($data, $hora_inicio, $hora_fim);
            $salaOptions = ArrayHelper::map($salas, 'id', 'nome');
        }

        if ($model->load(Yii::$app->request->post())) {

            $model->cliente_id = Yii::$app->user->id;
            $model->estado = $model::ESTADO_PENDENTE;

            if ($model->validateHorario()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Pedido de aluguer criado com sucesso.');
                    return $this->redirect(['aluguer-sala/view', 'id' => $model->id]);
                }
                else {
                    Yii::$app->session->setFlash('error', 'Erro ao criar o pedido de aluguer.');
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'cinemaOptions' => $cinemaOptions,
            'salaOptions' => $salaOptions,
        ]);
    }
    
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('eliminarAluguer', ['model' => $model])) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar este pedido de aluguer.');
            return $this->redirect(['aluguer-sala/index']);
        }

        if (!$model->isDeletable()) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar pedidos de aluguer já confirmados.');
            return $this->redirect(['aluguer-sala/index']);
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Pedido de aluguer eliminado com sucesso.');
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao eliminar o pedido de aluguer.');
        }


        return $this->redirect(['aluguer-sala/index']);
    }

    protected function findModel($id)
    {
        if (($model = AluguerSala::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}