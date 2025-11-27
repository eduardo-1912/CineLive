<?php

namespace backend\modules\api\controllers;

use common\models\Filme;
use common\models\Sessao;
use Yii;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class FilmeController extends Controller
{
    public $modelClass = 'common\models\Filme';

    public function actionIndex()
    {
        $cinemaId = Yii::$app->request->get('cinema_id');
        $kids = Yii::$app->request->get('kids');
        $estado = Yii::$app->request->get('estado');
        $q = Yii::$app->request->get('q');

        $query = Filme::find();

        if ($cinemaId) {
            Filme::findComSessoesFuturas($cinemaId);
        }

        if ($kids) {
            $query->andWhere(['filme.rating' => Filme::ratingsKids()]);
        }

        if ($estado) {
            $query->andWhere(['filme.estado' => $estado]);
        }

        if ($q) {
            $query->andWhere(['like', 'filme.titulo', $q]);
        }

        $filmes = $query->orderBy(['titulo' => SORT_ASC])->all();

        return $filmes;
    }

    public function actionView($id)
    {
        $filme = Filme::findOne($id);

        return [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
            'rating' => $filme->rating,
            'generos' => implode(', ', array_map(fn($g) => $g->nome, $filme->generos)),
            'estreia' => $filme->estreia,
            'duracao' => $filme->duracao,
            'idioma' => $filme->idioma,
            'realizacao' => $filme->realizacao,
            'sinopse' => $filme->sinopse,
        ];
    }

    public function actionSessaos($id)
    {
        $cinemaId = Yii::$app->request->get('cinema_id');

        // Verificar filme
        $filme = Filme::findOne($id);
        if (!$filme || $filme->isEstadoBrevemente()) {
            throw new NotFoundHttpException("Filme não encontrado ou ainda não disponível.");
        }

        $query = Sessao::find()
            ->where(['filme_id' => $id])
            ->andWhere(['>=', 'data', date('Y-m-d')])
            ->with(['cinema', 'sala'])
            ->orderBy(['data' => SORT_ASC, 'hora_inicio' => SORT_ASC]);

        // Filtro opcional por cinema
        if ($cinemaId) {
            $query->andWhere(['cinema_id' => $cinemaId]);
        }

        $sessoes = $query->all();

        if (!$sessoes) {
            throw new NotFoundHttpException($cinemaId ? "Não existem sessões deste filme neste cinema." : "Não existem sessões disponíveis para este filme.");
        }

        // AGRUPAR POR DATA
        $sessoesPorData = [];

        foreach ($sessoes as $sessao) {

            if (!$sessao->isEstadoAtiva()) continue;

            $data = $sessao->dataFormatada;

            // Base da sessão
            $dadosSessao = [
                'id' => $sessao->id,
                'hora_inicio' => $sessao->horaInicioFormatada,
                'hora_fim' => $sessao->horaFimFormatada,
            ];

            // ADICIONAR cinena APENAS SE cinema_id não veio no request
            if (!$cinemaId) {
                $dadosSessao['cinema_id'] = $sessao->cinema_id;
                $dadosSessao['cinema_nome'] = $sessao->cinema->nome;
            }

            $sessoesPorData[$data][] = $dadosSessao;
        }

        return $sessoesPorData;
    }
}