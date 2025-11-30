<?php

namespace frontend\controllers;

use common\components\Formatter;
use common\models\Cinema;
use frontend\helpers\CookieHelper;
use Yii;
use common\models\Filme;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class FilmeController extends Controller
{
    public function actionIndex($q = null, $cinema_id = null, $filter = null)
    {
        $cinemas = Cinema::findComSessoesAtivas();

        if ($filter === 'brevemente') {

            $query = Filme::find()->where(['estado' => Filme::ESTADO_BREVEMENTE]);

            if ($q) {
                $query->andWhere(['like', 'titulo', $q]);
            }

            $filmes = $query->orderBy(['titulo' => SORT_ASC])->all();

            return $this->render('index', [
                'filmes' => $filmes,
                'cinemas' => $cinemas,
                'cinemaSelecionado' => null,
                'cinema_id' => null,
                'filter' => $filter,
                'q' => $q,
            ]);
        }

        // Selecionar cinema
        if ($cinema_id == null) {
            CookieHelper::has('cinema_id')
                ? $cinema_id = CookieHelper::get('cinema_id')
                : $cinema_id = $cinemas[0]->id;
        }

        $cinemaSelecionado = Cinema::findOne($cinema_id);

        if ($cinemaSelecionado && !empty($cinemaSelecionado->getSessoesAtivas())) {
            CookieHelper::set('cinema_id', $cinema_id, 365);
        }
        else {
            return $this->redirect(['filme/index']);
        }

        $filmes = $cinemaSelecionado->getFilmesComSessoesAtivas($filter == 'kids', $q);

        return $this->render('index', [
            'filmes' => $filmes,
            'cinemas' => $cinemas,
            'cinemaSelecionado' => $cinemaSelecionado,
            'cinema_id' => $cinema_id,
            'filter' => $filter,
            'q' => $q,
        ]);
    }

    public function actionView($id, $cinema_id = null, $data = null, $sessao_id= null)
    {
        $model = $this->findModel($id);

        if (empty($model->getSessoesAtivas()) && !$model->isEstadoBrevemente()) {
            Yii::$app->session->setFlash('error', "Este filme já não está disponível.");
            return $this->redirect(['filme/index']);
        }

        $cinemaOptions = ArrayHelper::map($model->getCinemasComSessoesAtivas(), 'id', 'nome');
        $dataOptions = [];
        $horaOptions = [];

        if ($cinema_id && isset($cinemaOptions[$cinema_id])) {
            $sessoesPorData = $model->getSessoesAtivasPorData($cinema_id);
            $dataOptions = array_combine(array_keys($sessoesPorData), array_keys($sessoesPorData));

            if ($data  && isset($dataOptions[$data])) {
                $horaOptions = ArrayHelper::map(
                    $sessoesPorData[$data],
                    'id', fn($sessao) => Formatter::hora($sessao->hora_inicio)
                );
            }
        }

        return $this->render('view', [
            'model' => $model,
            'cinema_id' => $cinema_id,
            'cinemaOptions' => $cinemaOptions,
            'data' => $data,
            'dataOptions' => $dataOptions,
            'sessao_id' => $sessao_id,
            'horaOptions' => $horaOptions,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Filme::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
