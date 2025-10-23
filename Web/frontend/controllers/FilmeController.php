<?php

namespace frontend\controllers;

use Yii;
use common\models\Filme;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class FilmeController extends Controller
{
    public function actionIndex($q = null)
    {
        $query = Filme::find();

        // Se há termo de pesquisa
        if ($q) {
            $query->andWhere(['like', 'titulo', $q]);
        }

        // TODO: RETIRAR ISTO!!!
        $query->andWhere(['estado' => Filme::ESTADO_EM_EXIBICAO]);

        $filmes = $query->all();

        return $this->render('index', [
            'filmes' => $filmes,
            'q' => $q,
        ]);
    }

    public function actionView($id)
    {
        $model = Filme::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Filme não encontrado.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
