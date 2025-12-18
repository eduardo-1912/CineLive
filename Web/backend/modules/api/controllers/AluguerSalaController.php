<?php

namespace backend\modules\api\controllers;

use common\models\AluguerSala;
use common\models\Cinema;
use common\models\Sala;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AluguerSalaController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['cliente'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        return Yii::$app->user->identity->aluguerSalas;
    }

    public function actionView($id)
    {
        $user = Yii::$app->user;
        $model = AluguerSala::findOne($id);

        if (!$model || $model->cliente_id != $user->id) {
            throw new NotFoundHttpException("Aluguer não encontrado.");
        }

        return $model;
    }

    public function actionCreate()
    {
        $user = Yii::$app->user;
        $body = Yii::$app->request->bodyParams;

        // Verificar se todos os campos foram enviados
        $campos = ['data', 'hora_inicio', 'hora_fim', 'cinema_id', 'sala_id', 'tipo_evento', 'observacoes'];
        foreach ($campos as $campo) {
            $$campo = $body[$campo] ?? null;

            if (empty($$campo)) {
                throw new BadRequestHttpException("O campo '$campo' é obrigatório.");
            }
        }

        // Verificar se o cinema é válido
        $cinema = Cinema::findOne($cinema_id);
        if (!$cinema || !$cinema->isEstadoAtivo()) {
            throw new BadRequestHttpException('Cinema não encontrado.');
        }

        // Verificar se a sala é válida
        $salasDisponiveis = $cinema->getSalasDisponiveis($data, $hora_inicio, $hora_fim);
        if (!in_array(Sala::findOne($sala_id), $salasDisponiveis)) {
            throw new BadRequestHttpException("A sala escolhida não está disponível no horário selecionado.");
        }

        // Criar aluguer
        $model = new AluguerSala();
        $model->cliente_id = $user->id;
        $model->data = $data;
        $model->hora_inicio = $hora_inicio;
        $model->hora_fim = $hora_fim;
        $model->cinema_id = $cinema_id;
        $model->sala_id = $sala_id;
        $model->tipo_evento = $tipo_evento;
        $model->observacoes = $observacoes;
        $model->estado = $model::ESTADO_PENDENTE;

        if (!$model->validateHorario()) {
            throw new BadRequestHttpException("O horário selecionado não é válido.");
        }

        $model->save();
        return [
            'status' => 'success',
            'id' => $model->id,
        ];
    }

    public function actionUpdate($id)
    {
        $user = Yii::$app->user;
        $body = Yii::$app->request->bodyParams;
        $model = AluguerSala::findOne($id);

        if (!$model || $model->cliente_id != $user->id) {
            throw new NotFoundHttpException("Aluguer não encontrado.");
        }

        $sala_id = $body['sala_id'] ?? null;
        $estado = $body['estado'] ?? null;

        // Verificar se todos os campos foram enviados
        if (!$sala_id && !$estado) {
            throw new BadRequestHttpException("Faltam campos obrigatórios.");
        }

        // Verificar se a sala é válida
        $salasDisponiveis = $model->cinema->getSalasDisponiveis($model->data, $model->hora_inicio, $model->hora_fim, $model->sala_id);
        if (!in_array(Sala::findOne($sala_id), $salasDisponiveis)) {
            throw new BadRequestHttpException("A sala escolhida não está disponível no horário selecionado.");
        }

        // Verificar se o estado é válido
        if ($estado && !in_array($estado, array_keys(AluguerSala::optsEstadoBD()))) {
            throw new BadRequestHttpException("O estado inserido não é válido.");
        }

        // Atualizar dados
        if ($sala_id) $model->sala_id = $sala_id;
        if ($estado) $model->estado = $estado;
        $model->save();

        return [
            'status' => 'success',
            'id' => $model->id,
        ];
    }

    public function actionDelete($id)
    {
        $user = Yii::$app->user;
        $model = AluguerSala::findOne($id);

        if (!$model || $model->cliente_id != $user->id) {
            throw new NotFoundHttpException("Aluguer não encontrado.");
        }

        if (!$model->isEstadoPendente() && !$model->isEstadoCancelado()) {
            throw new ForbiddenHttpException("Só pode eliminar alugueres pendentes ou cancelados.");
        }

        $model->delete();
    }
}