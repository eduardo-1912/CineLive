<?php

namespace backend\modules\api\controllers;

use common\helpers\Formatter;
use common\models\Cinema;
use common\models\Filme;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class FilmeController extends Controller
{
    // region CRUD
    public function actionIndex($cinema_id = null, $filter = null, $q = null)
    {
        $kids = $filter === 'kids';
        $brevemente = $filter === 'brevemente';

        $cinema = Cinema::findOne($cinema_id) ?? null;

        if ($cinema_id && !$cinema || $cinema && !$cinema->isEstadoAtivo()) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        // Obter apenas filmes com sessões ativas desse cinema
        if ($cinema) $filmes = $cinema->getFilmesComSessoesAtivas($kids, $q);

        //Obter todos os filmes
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
            'estreia' => Formatter::data($filme->estreia),
            'duracao' => Formatter::horas($filme->duracao),
            'idioma' => $filme->idioma,
            'realizacao' => $filme->realizacao,
            'sinopse' => $filme->sinopse,
            'has_sessoes' => count($filme->getSessoesAtivas()) > 0,
        ];
    }
    // endregion

    // region ExtraPatterns
    // Sessões do filme (cinema opcional)
    public function actionSessoes($id, $cinema_id = null)
    {
        $filme = Filme::findOne($id);

        if (!$filme || $filme->isEstadoBrevemente()) {
            throw new NotFoundHttpException("Filme não encontrado ou ainda não disponível.");
        }

        // Obter as sessões por data do filme
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

    // Procurar filme por título
    public function actionPorTitulo($q)
    {
        $filmes = Filme::find()->where(['like', 'titulo', $q])->all();

        return array_map(fn($filme) => [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }

    // Contar filmes
    public function actionCount()
    {
        return Filme::find()->count();
    }

    // Contar sessões de um filme (cinema opcional)
    public function actionCountSessoes($id, $cinema_id = null)
    {
        return count(Filme::findOne($id)->getSessoesAtivas($cinema_id));
    }

    // Filmes mais vistos (limite opcional)
    public function actionMaisVistos($limit = null)
    {
        $filmes = Filme::find()
            ->joinWith(['sessoes', 'sessoes.compras compras'])
            ->select(['filme.id', 'filme.titulo', 'COUNT(compras.id) AS total'])
            ->groupBy('filme.id')
            ->orderBy(['total' => SORT_DESC])
            ->limit($limit)
            ->all();

        return array_map(fn($filme) => [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }

    // Filmes que estreiam hoje
    public function actionEstreiamHoje()
    {
        $filmes = Filme::find()->where(['estreia' => date('Y-m-d')])->all();

        return array_map(fn($filme) => [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }

    // Filmes por género
    public function actionPorGenero($genero)
    {
        $filmes = Filme::find()
            ->joinWith('generos g')
            ->where(['like', 'g.nome', $genero])
            ->all();

        return array_map(fn($f) => [
            'id' => $f->id,
            'titulo' => $f->titulo,
            'poster_url' => $f->posterUrl,
        ], $filmes);
    }

    // Filmes por idioma
    public function actionPorIdioma($idioma)
    {
        return Filme::find()->where(['idioma' => $idioma])->all();
    }
    // endregion
}