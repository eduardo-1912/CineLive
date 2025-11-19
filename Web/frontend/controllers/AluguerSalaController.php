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
            'model' => $model
        ]);
    }

    public function actionCreate($cinema_id = null, $data = null, $hora_inicio = null, $hora_fim = null)
    {
        $model = new AluguerSala();

        // preenche o model (para repor valores no formulário)
        $model->cinema_id = $cinema_id ?? Yii::$app->request->cookies->getValue('cinema_id', null);
        $model->data = $data ?? date('Y-m-d');
        $model->hora_inicio = $hora_inicio;
        $model->hora_fim = $hora_fim;

        // CINEMAS
        $cinemas = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])->all();
        $cinemasOptions = ArrayHelper::map($cinemas, 'id', 'nome');

        // SALAS DISPONÍVEIS
        $salasOptions = [];

        if ($cinema_id && $data && $hora_inicio && $hora_fim) {
            $salas = Sala::getSalasDisponiveis($cinema_id, $data, $hora_inicio, $hora_fim);

            foreach ($salas as $sala) {
                $salasOptions[$sala->id] = "{$sala->nome}  • {$sala->lugares} Lugares";
            }
        }

        if ($model->load(Yii::$app->request->post())) {

            $model->cliente_id = Yii::$app->user->id;
            $model->estado = AluguerSala::ESTADO_PENDENTE;

            if ($model->validateHorario()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Pedido de aluguer enviado com sucesso!');
                    return $this->redirect(['aluguer-sala/view', 'id' => $model->id]);
                }
                else {
                    Yii::$app->session->setFlash('error', 'Ocorreu um erro ao enviar o pedido.');

                }
            }
            else {
                Yii::$app->session->setFlash('error', 'O horário selecionado é inválido.');
            }

        }

        return $this->render('create', [
            'model' => $model,
            'cinemasOptions' => $cinemasOptions,
            'salasOptions' => $salasOptions,
        ]);
    }

    
    public function actionDelete($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        if ($currentUser->id != $model->cliente_id) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar este pedido de aluguer de sala.');
            return $this->redirect(Yii::$app->request->referrer ?: ['aluguer-sala/index']);
        }

        if ($model->isDeletable())
        {
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Pedido de aluguer eliminado com sucesso.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar o pedido de aluguer.');
            }
        }
        else {
            Yii::$app->session->setFlash('error', 'Não pode eliminar pedidos de aluguer já confirmados.');
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