<?php

namespace backend\modules\api\controllers;

use common\helpers\Formatter;
use common\models\Cinema;
use common\models\Filme;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class FilmeController extends Controller
{
    public function actionIndex($cinema_id = null, $filter = null, $q = null)
    {
        $kids = $filter === 'kids';
        $brevemente = $filter === 'brevemente';

        $cinema = Cinema::findOne($cinema_id) ?? null;

        if ($cinema_id && !$cinema) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        // Se tem cinema --> obter apenas filmes com sessões ativas desse cinema
        if ($cinema) {
            if (!$cinema->isEstadoAtivo()) {
                throw new NotFoundHttpException("Cinema não encontrado.");
            }

            $filmes = $cinema->getFilmesComSessoesAtivas($kids, $q);
        }

        // Caso contrário --> obter todos os filmes
        else {
            $filmes = Filme::find();
            if ($kids) $filmes->andWhere(['rating' => Filme::optsRatingKids()]);
            if ($brevemente) $filmes->andWhere(['estado' => Filme::ESTADO_BREVEMENTE]);
            if ($q) $filmes->andWhere(['like', 'titulo', $q]);
            $filmes = $filmes->orderBy(['titulo' => SORT_ASC])->all();
        }

        return array_map(fn($filme) => [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }

    public function actionView($id)
    {
        $filme = Filme::findOne($id);

        return [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
            'rating' => $filme->rating,
            'generos' => implode(', ', array_map(fn($genero) => $genero->nome, $filme->generos)),
            'estreia' => $filme->estreia,
            'duracao' => Formatter::horas($filme->duracao),
            'idioma' => $filme->idioma,
            'realizacao' => $filme->realizacao,
            'sinopse' => $filme->sinopse,
        ];
    }

    public function actionSessoes($id, $cinema_id = null)
    {
        $filme = Filme::findOne($id);

        if (!$filme || $filme->isEstadoBrevemente()) {
            throw new NotFoundHttpException("Filme não encontrado ou ainda não disponível.");
        }

        $sessoes = $filme->getSessoesAtivasPorData($cinema_id);

        return array_map(fn($sessoesPorData) =>
            array_map(fn($sessao) => [
                'id'          => $sessao->id,
                'hora_inicio' => Formatter::hora($sessao->hora_inicio),
                'hora_fim'    => Formatter::hora($sessao->hora_fim),
                'cinema_id'   => $sessao->cinema_id,
            ], $sessoesPorData),
        $sessoes);
    }
}