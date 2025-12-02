<?php

namespace backend\controllers;

use common\models\Cinema;
use Yii;
use common\models\Compra;
use backend\models\CompraSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class CompraController extends Controller
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
                        'roles' => ['admin', 'gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['index', 'view', 'confirm-all-tickets'],
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
        $userCinema = $currentUser->identity->profile->cinema;

        $gerirCinemas = $currentUser->can('gerirCinemas');
        $verTodasCompras = $currentUser->can('verTodasCompras');
        $verComprasCinema = $currentUser->can('verComprasCinema', ['model' => $userCinema]);

        if (!$verTodasCompras && !$verComprasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver compras.');
            return $this->goHome();
        }

        $searchModel = new CompraSearch();
        $params = Yii::$app->request->queryParams;

        if ($verComprasCinema && $userCinema) {
            $params['CompraSearch']['cinema_id'] = $userCinema->id;
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'gerirCinemas' => $gerirCinemas,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cinemaOptions' => Cinema::findAllList(),
            'estadoOptions' => Compra::optsEstado(),
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirCinemas = $currentUser->can('gerirCinemas');
        $verTodasCompras = $currentUser->can('verTodasCompras');
        $verComprasCinema = $currentUser->can('verComprasCinema', ['model' => $model->sessao->cinema]);

        if (!$verTodasCompras && !$verComprasCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta compra.');
            return $this->redirect(['index']);
        }

        $bilhetesDataProvider = new ActiveDataProvider([
            'query' => $model->getBilhetes(),
            'pagination' => false,
        ]);

        return $this->render('view', [
            'model' => $model,
            'gerirCinemas' => $gerirCinemas,
            'bilhetesDataProvider' => $bilhetesDataProvider,
        ]);
    }

    public function actionConfirmAllTickets($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $confirmarBilhetes = $currentUser->can('confirmarBilhetes');
        $confirmarBilhetesCinema = $currentUser->can('confirmarBilhetesCinema', ['model' => $model->sessao->cinema]);

        if (!$confirmarBilhetes && !$confirmarBilhetesCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para confirmar os bilhetes desta compra.');
            return $this->redirect(['index']);
        }

        if ($model->sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não pode confirmar bilhetes de uma sessão que já terminou.');
            return $this->redirect(['index']);
        }

        if ($model->isTodosBilhetesConfirmados()) {
            Yii::$app->session->setFlash('info', 'Todos os bilhetes desta compra já foram confirmados.');
            return $this->redirect(['index']);
        }

        // Obter bilhetes da compra
        $bilhetes = $model->bilhetes;
        if (empty($bilhetes)) {
            Yii::$app->session->setFlash('info', 'Esta compra não possui bilhetes.');
            return $this->redirect(['index']);
        }

        $confirmados = 0;
        foreach ($bilhetes as $bilhete) {
            if ($bilhete->estado === $bilhete::ESTADO_PENDENTE) {
                $bilhete->estado = $bilhete::ESTADO_CONFIRMADO;
                $bilhete->save(false, ['estado']);
                $confirmados++;
            }
        }

        if ($confirmados > 0) {
            Yii::$app->session->setFlash('success', "Foram confirmados {$confirmados} bilhetes.");
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    protected function findModel($id)
    {
        if (($model = Compra::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
