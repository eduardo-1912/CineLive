<?php

namespace backend\modules\api\controllers;

use common\helpers\Formatter;
use common\models\Sessao;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class SessoesController extends Controller
{
    public function actionIndex()
    {
        $sessoes = array_filter(Sessao::find()->all(), fn($sessao) => $sessao->isEstadoAtiva());

        return array_map(fn($sessao) => [
            'id' => $sessao->id,
            'data' => Formatter::data($sessao->data),
            'hora_inicio' => Formatter::hora($sessao->hora_inicio),
            'hora_fim' => Formatter::hora($sessao->hora_fim),
            'filme_id' => $sessao->filme_id,
            'cinema_id' => $sessao->cinema_id,
            'sala_id' => $sessao->sala_id,
        ], $sessoes);
    }

    public function actionView($id)
    {
        $sessao = Sessao::findOne($id);

        if (!$sessao || !$sessao->isEstadoAtiva()) {
            throw new NotFoundHttpException("Sessão não encontrada.");
        }

        return [
            'id' => $sessao->id,
            'data' => Formatter::data($sessao->data),
            'hora_inicio' => Formatter::hora($sessao->hora_inicio),
            'hora_fim' => Formatter::hora($sessao->hora_fim),
            'filme_id' => $sessao->filme_id,
            'cinema_id' => $sessao->cinema_id,
            'cinema_nome' => $sessao->cinema->nome,
            'sala' => [
                'id' => $sessao->sala->id,
                'nome' => $sessao->sala->nome,
                'preco_bilhete' => (float)$sessao->sala->preco_bilhete,
                'num_filas' => $sessao->sala->num_filas,
                'num_colunas' => $sessao->sala->num_colunas,
                'lugares_ocupados' => $sessao->lugaresOcupados,
            ],
        ];
    }
}