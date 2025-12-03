<?php

namespace backend\modules\api\controllers;

use common\models\Cinema;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class CinemaController extends Controller
{
    public function actionIndex()
    {
        $cinemas = Cinema::findAtivos();

        return array_map(fn($cinema) => [
            'id' => $cinema->id,
            'nome' => $cinema->nome,
            'morada' => $cinema->morada,
            'telefone' => "{$cinema->telefone}",
            'email' => $cinema->email,
            'horario' => $cinema->horario,
            'capacidade' => "{$cinema->numeroSalas} Salas • {$cinema->numeroLugares} Lugares",
        ], $cinemas);
    }

    public function actionList()
    {
        return Cinema::findAtivosList();
    }

    public function actionView($id)
    {
        $cinema = Cinema::findOne($id);

        if (!$cinema || !$cinema->isEstadoAtivo()) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        return [
            'id' => $cinema->id,
            'nome' => $cinema->nome,
            'morada' => $cinema->morada,
            'telefone' => $cinema->telefone,
            'email' => $cinema->email,
            'horario' => $cinema->horario,
            'capacidade' => "{$cinema->numeroSalas} Salas • {$cinema->numeroLugares} Lugares",
        ];
    }

    public function actionFilmes($id, $filter = null, $q =null)
    {
        $cinema = Cinema::findOne($id);

        if (!$cinema || !$cinema->isEstadoAtivo()) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        $filmes = $cinema->getFilmesComSessoesAtivas($filter === 'kids', $q);

        return array_map(fn($filme) => [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }
}