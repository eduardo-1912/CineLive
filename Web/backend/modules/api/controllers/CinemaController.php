<?php

namespace backend\modules\api\controllers;

use common\models\Cinema;
use common\models\Filme;
use Yii;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class CinemaController extends Controller
{
    public function actionIndex()
    {
        $cinemas = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])->all();

        return array_map(fn($cinema) => [
            'id' => $cinema->id,
            'nome' => $cinema->nome,
            'morada' => $cinema->morada,
            'telefone' => "{$cinema->telefone}",
            'email' => $cinema->email,
            'horario' => $cinema->horario,
            'capacidade' => "{$cinema->totalSalas} Salas • {$cinema->numeroLugares} Lugares",
        ], $cinemas);
    }

    public function actionSimple()
    {
        $cinemas = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])->all();

        return array_map(fn($cinema) => [
            'id' => $cinema->id,
            'nome' => $cinema->nome,
        ], $cinemas);
    }

    public function actionView($id)
    {
        $cinema = Cinema::findOne($id);

        if (!$cinema || $cinema->estado !== Cinema::ESTADO_ATIVO) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        return $cinema;
    }

    public function actionFilmes($id)
    {
        $cinema = Cinema::findOne($id);

        if (!$cinema || !$cinema->isEstadoAtivo()) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        $kids = Yii::$app->request->get('kids');
        $q = Yii::$app->request->get('q');

        $query = Filme::findComSessoesFuturas($id);

        if ($kids) {
            $query->andWhere(['rating' => Filme::ratingsKids()]);
        }

        if ($q) {
            $query->andWhere(['like', 'titulo', $q]);
        }

        // Ordernar por título
        $filmes = $query->orderBy(['titulo' => SORT_ASC])->all();

        // Apenas ter filmes com sessões ativas (não esgotadas)
        $filmes = array_filter($filmes, fn($filme) => $filme->hasSessoesAtivas());

        return array_map(fn($filme) => [
            'id'     => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }
}