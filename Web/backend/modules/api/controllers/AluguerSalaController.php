<?php

namespace backend\modules\api\controllers;

use common\models\AluguerSala;
use common\models\Cinema;
use common\models\Sala;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

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

        return $behaviors;
    }

    public function actionIndex()
    {
        $user = Yii::$app->user;
        $userCinema = $user->identity->profile->cinema ?? null;

        $gerirAlugueres = $user->can('gerirAlugueres');
        $gerirAlugueresCinema = $user->can('gerirAlugueresCinema', ['model' => $userCinema]);
        $verAlugueresCinema = $user->can('verAlugueresCinema', ['model' => $userCinema]);

        if ($gerirAlugueres) {
            $alugueres = AluguerSala::find()->all();
        }
        if (($gerirAlugueresCinema || $verAlugueresCinema) && $userCinema) {
            $alugueres = $userCinema->aluguerSalas;
        }
        else {
            $alugueres = $user->identity->aluguerSalas;
        }

        return $alugueres;
    }

    public function actionView($id)
    {
        $user = Yii::$app->user;
        $model = AluguerSala::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException("Aluguer não encontrado.");
        }

        $gerirAlugueres = $user->can('gerirAlugueres');
        $gerirAlugueresCinema = $user->can('gerirAlugueresCinema', ['model' => $model->cinema]);
        $verAlugueresCinema = $user->can('verAlugueresCinema', ['model' => $model->cinema]);
        $verAlugueres = $user->can('verAlugueres', ['model' => $model]);

        if ($gerirAlugueres || $gerirAlugueresCinema || $verAlugueresCinema || $verAlugueres) {
            return $model;
        }

        throw new NotFoundHttpException("Aluguer não encontrado.");
    }

    public function actionCreate()
    {
        $user = Yii::$app->user;

        if (!$user->can('criarAluguer')) {
            throw new ForbiddenHttpException("Não tem permissão para criar alugueres.");
        }

        $body = Yii::$app->request->bodyParams;

        $data = $body['data'] ?? null;
        $hora_inicio = $body['hora_inicio'] ?? null;
        $hora_fim = $body['hora_fim'] ?? null;
        $cinema_id = $body['cinema_id'] ?? null;
        $sala_id = $body['sala_id'] ?? null;
        $tipo_evento = $body['tipo_evento'] ?? null;
        $observacoes = $body['observacoes'] ?? null;

        if (!$data || !$hora_inicio || !$hora_fim || !$cinema_id || !$sala_id || !$tipo_evento || !$observacoes) {
            throw new BadRequestHttpException('Faltam campos obrigatórios.');
        }

        $cinema = Cinema::findOne($cinema_id);

        if (!$cinema || !$cinema->isEstadoAtivo()) {
            throw new BadRequestHttpException('Cinema não encontrado.');
        }

        $sala = Sala::findOne($sala_id);
        $salasDisponiveis = $cinema->getSalasDisponiveis($data, $hora_inicio, $hora_fim);

        if (!in_array($sala, $salasDisponiveis)) {
            throw new BadRequestHttpException('A sala escolhida não está disponível no horário selecionado.');
        }

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
            throw new BadRequestHttpException('O horário selecionado não é válido.');
        }

        $model->save();
    }

    public function actionUpdate($id)
    {
        $user = Yii::$app->user;
        $model = AluguerSala::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException("Aluguer não encontrado.");
        }

        $gerirAlugueres = $user->can('gerirAlugueres');
        $gerirAlugueresCinema = $user->can('gerirAlugueresCinema', ['model' => $model->cinema]);

        if (!$gerirAlugueres && !$gerirAlugueresCinema) {
            throw new UnauthorizedHttpException("Não tem permissão para editar este aluguer.");
        }

        $body = Yii::$app->request->bodyParams;

        $sala_id = $body['sala_id'] ?? null;
        $estado = $body['estado'] ?? null;

        if (!$sala_id && !$estado) {
            throw new BadRequestHttpException('Faltam campos obrigatórios.');
        }

        $sala = Sala::findOne($sala_id);
        $salasDisponiveis = $model->cinema->getSalasDisponiveis($model->data, $model->hora_inicio, $model->hora_fim, $model->sala_id);

        if (!in_array($sala, $salasDisponiveis)) {
            throw new BadRequestHttpException('A sala escolhida não está disponível no horário selecionado.');
        }

        if (!in_array($estado, array_keys(AluguerSala::optsEstadoBD()))) {
            throw new BadRequestHttpException('O estado inserido não é válido.');
        }

        if ($sala) {
            $model->sala_id = $sala_id;
        }

        if ($estado) {
            $model->estado = $estado;
        }

        $model->save();
    }

    public function actionDelete($id)
    {
        $user = Yii::$app->user;
        $model = AluguerSala::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException("Aluguer não encontrado.");
        }

        $gerirAlugueres = $user->can('gerirAlugueres');
        $gerirAlugueresCinema = $user->can('gerirAlugueresCinema', ['model' => $model->cinema]);
        $verAlugueres = $user->can('verAlugueres', ['model' => $model]);

        if (!$gerirAlugueres && !$gerirAlugueresCinema && !$verAlugueres) {
            throw new ForbiddenHttpException('Só pode eliminar alugueres pendentes ou cancelados.');
        }

        if ($model->isEstadoPendente() || $model->isEstadoCancelado()) {
            $model->delete();
        }
    }
}