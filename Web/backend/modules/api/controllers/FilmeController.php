<?php

namespace backend\modules\api\controllers;

use common\models\Filme;
use common\models\Sessao;
use Yii;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class FilmeController extends ActiveController
{
    public $modelClass = 'common\models\Filme';

    /*public function actions()
    {
        $actions = parent::actions();

        unset($actions['create'], $actions['update'], $actions['delete'],);

        return $actions;
    }*/

    public function actions()
    {
        $actions = parent::actions();

        // Bloquear métodos não autorizados
        $notAllowedActions = ['create', 'update', 'delete'];

        foreach ($notAllowedActions as $action) {
            $actions[$action] = fn() => throw new MethodNotAllowedHttpException;
        }

        // Remover index default para usar filtros
        unset($actions['index']);

        return $actions;
    }

    public function actionIndex()
    {
        $cinemaId = Yii::$app->request->get('cinema_id');
        $kids = Yii::$app->request->get('kids');
        $estado = Yii::$app->request->get('estado');
        $q = Yii::$app->request->get('q');

        $query = Filme::find();

        // Filtrar por cinema (apenas com sessões futuras)
        if ($cinemaId) {
            $query->joinWith('sessaos s')
                ->andWhere(['s.cinema_id' => $cinemaId])
                ->andWhere(['>=', 's.data', date('Y-m-d')])
                ->distinct();
        }
        // TODO: NÃO MOSTRAR SESSÕES ESGOTADAS

        // Filtrar para crianças
        if ($kids) {
            $query->andWhere(['filme.rating' => Filme::ratingsKids()]);
        }

        // Filtrar por estado
        if ($estado) {
            $query->andWhere(['filme.estado' => $estado]);
        }

        // Filtrar por título
        if ($q) {
            $query->andWhere(['like', 'filme.titulo', $q]);
        }

        return $query->all();
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