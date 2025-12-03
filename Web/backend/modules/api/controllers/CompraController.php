<?php

namespace backend\modules\api\controllers;

use common\helpers\Formatter;
use common\models\Bilhete;
use common\models\Compra;
use common\models\Sessao;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CompraController extends Controller
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
        $user = Yii::$app->user->identity;
        $compras = $user->getCompras()->orderBy(['id' => SORT_DESC])->all();

        return array_map(fn($compra) => [
            'id' => $compra->id,
            'cliente_id' => $compra->cliente_id,
            'data' => Formatter::data($compra->data),
            'total' => Formatter::preco($compra->total),
            'estado' => $compra->displayEstado(),

            'filme_id' => $compra->sessao->filme_id,
            'filme_nome' => $compra->sessao->filme->titulo,

            'cinema_id' => $compra->sessao->cinema_id,
            'cinema_nome' => $compra->sessao->cinema->nome,

            'sessao_id' => $compra->sessao->id,
            'sessao_data' => Formatter::data($compra->sessao->data),
            'sessao_hora_inicio' => Formatter::hora($compra->sessao->hora_inicio),
        ], $compras);
    }

    public function actionView($id)
    {
        $userId = Yii::$app->user->id;
        $compra = Compra::findOne($id);

        if (!$compra || $compra->cliente_id != $userId) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        return [
            'id' => $compra->id,
            'cliente_id' => $compra->cliente_id,
            'data' => Formatter::data($compra->data),
            'total' => Formatter::preco($compra->total),
            'estado' => $compra->displayEstado(),

            'filme_id' => $compra->sessao->filme_id,
            'filme_titulo' => $compra->sessao->filme->titulo,

            'cinema_id' => $compra->sessao->cinema_id,
            'cinema_nome' => $compra->sessao->cinema->nome,

            'sala_id' => $compra->sessao->sala_id,
            'sala_nome' => $compra->sessao->sala->nome,

            'sessao_id' => $compra->sessao->id,
            'sessao_data' => Formatter::data($compra->sessao->data),
            'sessao_horario' => $compra->sessao->horario,

            'bilhetes' => array_map(fn($bilhete) => [
                'id' => $bilhete->id,
                'lugar' => $bilhete->lugar,
                'preco' => Formatter::preco($bilhete->preco),
                'estado' => $bilhete->displayEstado(),
            ], $compra->bilhetes)
        ];
    }

    public function actionCreate()
    {
        $userId = Yii::$app->user->id;
        $body = Yii::$app->request->bodyParams;

        $sessaoId = $body['sessao_id'] ?? null;
        $pagamento = $body['pagamento'] ?? null;
        $lugares = $body['lugares'] ?? null;

        if (!$sessaoId || !$pagamento || !$lugares) {
            throw new BadRequestHttpException('Faltam campos obrigatórios.');
        }

        $sessao = Sessao::findOne($sessaoId);

        if (!$sessaoId || !$sessao->isEstadoAtiva()) {
            throw new BadRequestHttpException("Sessão inválida.");
        }

        foreach ($lugares as $lugar) {
            if (!in_array($lugar, $sessao->sala->lugares)) {
                return [
                    'status' => 'error',
                    'message' => "O lugar $lugar não é válido."
                ];
            }
            if (in_array($lugar, $sessao->lugaresOcupados)) {
                return [
                    'status' => 'error',
                    'message' => "O lugar $lugar já está ocupado."
                ];
            }
        }

        // 1. Criar compra
        $compra = new Compra();
        $compra->cliente_id = $userId;
        $compra->sessao_id = $sessaoId;
        $compra->data = date('Y-m-d H:i:s');
        $compra->pagamento = $pagamento;
        $compra->estado = $compra::ESTADO_CONFIRMADA;

        if (!$compra->save()) {
            return [
                'status' => 'error',
                'errors' => $compra->errors
            ];
        }

        // 2. Criar bilhetes
        $bilhetes = [];

        foreach ($lugares as $lugar) {
            $bilhete = new Bilhete();
            $bilhete->compra_id = $compra->id;
            $bilhete->lugar = $lugar;
            $bilhete->preco = $sessao->sala->preco_bilhete;
            $bilhete->codigo = $bilhete::gerarCodigo();
            $bilhete->estado = $bilhete::ESTADO_PENDENTE;

            if (!$bilhete->save()) {
                $compra->delete();

                return [
                    'status' => 'error',
                    'errors' => $bilhete->errors
                ];
            }

            $bilhetes[] = [
                'id' => $bilhete->id,
                'codigo' => $bilhete->codigo,
                'lugar' => $bilhete->lugar,
                'preco' => $bilhete->preco,
            ];
        }

        return [
            'status' => 'success',
            'compra_id' => $compra->id,
            'total' => $compra->total,
            'bilhetes' => $bilhetes
        ];
    }
}