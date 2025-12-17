<?php

namespace backend\modules\api\controllers;

use common\models\Cinema;
use common\models\Sessao;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class CinemaController extends Controller
{
    // region CRUD
    public function actionIndex($q = null)
    {
        $cinemas = $q
            ? Cinema::find()
            ->where(['estado' => Cinema::ESTADO_ATIVO])
            ->andWhere(['like', 'nome', $q])
            ->all()
            : Cinema::findAtivos();

        // Ordernar por sessões ativas
        usort($cinemas, function($a, $b) {
            return (bool)$b->getSessoesAtivas() <=> (bool)$a->getSessoesAtivas();
        });

        return array_map(fn($cinema) => [
            'id' => $cinema->id,
            'nome' => $cinema->nome,
            'morada' => $cinema->morada,
            'telefone' => "{$cinema->telefone}",
            'email' => $cinema->email,
            'horario' => $cinema->horario,
            'capacidade' => "{$cinema->numeroSalas} Salas • {$cinema->numeroLugares} Lugares",
            'has_sessoes' => (bool)$cinema->getSessoesAtivas(),
        ], $cinemas);
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
            'has_sessoes' => (bool)$cinema->getSessoesAtivas(),
        ];
    }
    // endregion

    // region ExtraPatterns
    // Filmes em exibição do cinema
    public function actionFilmes($id, $filter = null, $q =null)
    {
        $cinema = Cinema::findOne($id);

        if (!$cinema || !$cinema->isEstadoAtivo()) {
            throw new NotFoundHttpException("Cinema não encontrado.");
        }

        // Obter filmes com sessões ativas desse cinema
        $filmes = $cinema->getFilmesComSessoesAtivas($filter === 'kids', $q);

        return array_map(fn($filme) => [
            'id' => $filme->id,
            'titulo' => $filme->titulo,
            'poster_url' => $filme->posterUrl,
        ], $filmes);
    }

    // Contar sessões
    public function actionCountSessoes($id)
    {
        $cinema = Cinema::findOne($id);

        return count($cinema->getSessoesAtivas());
    }
    // endregion
}