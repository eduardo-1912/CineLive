<?php

namespace backend\controllers;

use common\models\Cinema;
use Yii;
use common\models\AluguerSala;
use backend\models\AluguerSalaSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
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
        $gerirAlugueres = $currentUser->can('gerirAlugueres');
        $gerirAlugueresCinema = $currentUser->can('gerirAlugueresCinema', ['model' => $userCinema]);
        $verAlugueresCinema = $currentUser->can('verAlugueresCinema', ['model' => $userCinema]);

        if (!$gerirAlugueres && !$gerirAlugueresCinema && !$verAlugueresCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver alugueres.');
            return $this->goHome();
        }

        $searchModel = new AluguerSalaSearch();
        $params = Yii::$app->request->queryParams;

        if ($userCinema && ($gerirAlugueresCinema || $verAlugueresCinema)) {
            $params['AluguerSalaSearch']['cinema_id'] = $userCinema->id;
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gerirCinemas' => $gerirCinemas,
            'gerirAlugueres' => $gerirAlugueres,
            'cinemaOptions' => Cinema::findAllList(),
            'estadoOptions' => AluguerSala::optsEstadoBD(),
        ]);
    }

    public function actionView($id)
    {
        $currentUser =Yii::$app->user;
        $model = $this->findModel($id);

        $gerirAlugueres = $currentUser->can('gerirAlugueres');
        $gerirAlugueresCinema = $currentUser->can('gerirAlugueresCinema', ['model' => $model->cinema]);
        $verAlugueresCinema = $currentUser->can('verAlugueresCinema', ['model' => $model->cinema]);

        if (!$gerirAlugueres && !$gerirAlugueresCinema && !$verAlugueresCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver este aluguer.');
            return $this->goHome();
        }

        if (!$model->isEditable()) {
            Yii::$app->session->setFlash('error', 'Não é possível editar este aluguer.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Obter salas disponíveis
        $salas = $model->cinema->getSalasDisponiveis($model->cinema_id, $model->data, $model->hora_inicio, $model->hora_fim, $model->sala_id);
        $salaOptions = ArrayHelper::map($salas, 'id', 'nome');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Pedido de aluguer atualizado com sucesso.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar o pedido de aluguer.');
            }

            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'model' => $model,
            'gerirAlugueres' => $gerirAlugueres,
            'gerirAlugueresCinema' => $gerirAlugueresCinema,
            'salaOptions' => $salaOptions,
            'estadoOptions' => $model->getEstadoOptions(),
        ]);
    }

    public function actionChangeStatus($id, $estado)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirAlugueres = $currentUser->can('gerirAlugueres');
        $gerirAlugueresCinema = $currentUser->can('gerirAlugueresCinema', ['model' => $model->cinema]);

        if (!$gerirAlugueres && !$gerirAlugueresCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar este aluguer.');
            return $this->goHome();
        }

        if ($model->isEstadoCancelado()) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar o estado de um aluguer já cancelado.');
            return $this->redirect(['index']);
        }

        if (!$model->isEditable())
        {
            Yii::$app->session->setFlash('error', 'Não é possível cancelar um aluguer que já começou ou terminou.');
            return $this->redirect(['index']);
        }

        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', 'O aluguer já se encontra neste estado.');
            return $this->redirect(['index']);
        }

        // Atualizar Estado
        $model->estado = $estado;

        if ($model->save(false, ['estado'])) {
            Yii::$app->session->setFlash('success', 'Estado do aluguer atualizado com sucesso.');

        } else {
            Yii::$app->session->setFlash('error', 'Erro ao atualizar o estado do aluguer.');
        }


        return $this->redirect(['index']);
    }

    public function actionDelete($id)
    {
        $currentUser =Yii::$app->user;
        $model = $this->findModel($id);

        $gerirAlugueres = $currentUser->can('gerirAlugueres');
        $gerirAlugueresCinema = $currentUser->can('gerirAlugueresCinema', ['model' => $model->cinema]);

        if (!$gerirAlugueres && !$gerirAlugueresCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar este aluguer.');
            return $this->goHome();
        }

        if ($model->isDeletable()) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Aluguer eliminado com sucesso.');
        }
        else {
            Yii::$app->session->setFlash('error', 'Não pode eliminar alugueres confirmados.');
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = AluguerSala::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
