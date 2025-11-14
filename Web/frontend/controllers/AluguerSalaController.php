<?php

namespace frontend\controllers;

use common\models\Cinema;
use common\models\Sala;
use frontend\models\ContactForm;
use Yii;
use common\models\AluguerSala;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;



class AluguerSalaController extends Controller
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
                        'roles' => ['cliente'],
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

    public function actionIndex()
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user->identity;

        $alugueres = $currentUser->getAlugueres()->orderBy(['id' => SORT_DESC])->all();

        return $this->render('index', [
            'alugueres' => $alugueres,
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        if ($currentUser->id != $model->cliente_id) {
            return $this->redirect(Yii::$app->request->referrer ?: ['aluguer-sala/index']);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate(){

        $model = new AluguerSala();

        $currentUser = Yii::$app->user->identity;

        $nomeCliente = $currentUser->profile->nome ?? '-';
        $emailCliente = $currentUser->email ?? '-';
        $telemovelCliente = $currentUser->profile->telemovel ?? '-';
        $nomeCinema = $model->cinema->nome ?? '-';

        //OBTER CINEMAS DISPONIVEIS
        $cinemasOptions = ArrayHelper::map(Cinema::Find()->where(['estado' => Cinema::ESTADO_ATIVO])->orderBy('nome')->all(), 'id', 'nome');
        $cinemaId = Yii::$app->request->get('cinema_id');

        if ($cinemaId !== null) {
            $model->cinema_id = $cinemaId;  // <-- ESSENCIAL
        }

        // SALAS DISPONÍVEIS
        $salasDisponiveis = Sala::getSalasDisponiveis($model->cinema_id, $model->data, $model->hora_inicio, $model->hora_fim, $model->sala_id);
        $salasDisponiveis = ArrayHelper::map($salasDisponiveis, 'id', function ($sala){
            $lugares = $sala->num_filas * $sala->num_colunas;
            return "{$sala->nome} - {$lugares} lugares";
        });


        return $this->render('create', [
            'model' => $model,
            'nomeCliente'=>$nomeCliente,
            'emailCliente'=>$emailCliente,
            'telemovelCliente'=>$telemovelCliente,
            'nomeCinema'=>$nomeCinema,
            'salasDisponiveis'=>$salasDisponiveis,
            'cinemasOptions'=>$cinemasOptions,
        ]);

    }

    public function actionAluguerSalaForm()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }


    protected function findModel($id)
    {
        if (($model = AluguerSala::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}