<?php

namespace backend\modules\api\controllers;

use common\models\Filme;
use Yii;
use yii\rest\ActiveController;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class FilmeController extends ActiveController
{
    public $modelClass = 'common\models\Filme';

    /*public function actions()
    {
        $actions = parent::actions();

        unset($actions['create'], $actions['update'], $actions['delete'],);

        return $actions;
    }*/

    public function actions()
    {
        $actions = parent::actions();

        // Bloquear métodos não autorizados
        $notAllowedActions = ['create', 'update', 'delete'];

        foreach ($notAllowedActions as $action) {
            $actions[$action] = fn() => throw new MethodNotAllowedHttpException;
        }

        // Remover index default para usar filtros
        unset($actions['index']);

        return $actions;
    }

    public function actionIndex()
    {
        $cinemaId = Yii::$app->request->get('cinema_id');
        $kids = Yii::$app->request->get('kids');
        $estado = Yii::$app->request->get('estado');
        $q = Yii::$app->request->get('q');

        $query = Filme::find();

        // Filtrar por cinema (apenas com sessões futuras)
        if ($cinemaId) {
            $query->joinWith('sessaos s')
                ->andWhere(['s.cinema_id' => $cinemaId])
                ->andWhere(['>=', 's.data', date('Y-m-d')])
                ->distinct();
        }
        // TODO: NÃO MOSTRAR SESSÕES ESGOTADAS

        // Filtrar para crianças
        if ($kids) {
            $query->andWhere(['filme.rating' => Filme::ratingsKids()]);
        }

        // Filtrar por estado
        if ($estado) {
            $query->andWhere(['filme.estado' => $estado]);
        }

        // Filtrar por título
        if ($q) {
            $query->andWhere(['like', 'filme.titulo', $q]);
        }

        return $query->all();
    }

    public function actionSessaos($id)
    {
        $cinemaId = Yii::$app->request->get('cinema_id');

        $filme = Filme::findOne($id);

        if (!$filme || !$filme->sessaos || $filme->isEstadoBrevemente()) {
            throw new NotFoundHttpException;
        }

        $sessaos = $filme->sessaos;

        return $sessaos;
    }
}