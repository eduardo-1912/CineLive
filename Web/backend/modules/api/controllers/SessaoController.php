<?php

namespace backend\modules\api\controllers;

use common\models\Sessao;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class SessaoController extends Controller
{
    public function actionView($id)
    {
        $sessao = Sessao::find()
            ->where(['id' => $id])
            ->with(['cinema', 'sala', 'filme']) // Eager loading (reduz queries)
            ->one();

        if (!$sessao || !$sessao->isEstadoAtiva()) {
            throw new NotFoundHttpException("Sessão não encontrada.");
        }

        return [
            'id' => $sessao->id,
            'data' => $sessao->dataFormatada,
            'hora_inicio' => $sessao->horaInicioFormatada,
            'hora_fim' => $sessao->horaFimFormatada,

            'filme_id' => $sessao->filme_id,
            'filme_titulo' => $sessao->filme->titulo,
            'filme_poster' => $sessao->filme->getPosterUrl(),
            'filme_rating' => $sessao->filme->rating,
            'filme_duracao' => $sessao->filme->duracaoEmHoras,

            'cinema_id' => $sessao->cinema_id,
            'cinema_nome' => $sessao->cinema->nome,

            'sala' => [
                'id' => $sessao->sala->id,
                'nome' => $sessao->sala->nome,
                'num_filas' => $sessao->sala->num_filas,
                'num_colunas' => $sessao->sala->num_colunas,
                'lugares_ocupados' => $sessao->lugaresOcupados,
            ],

        ];
    }
}