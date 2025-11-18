<?php

namespace backend\modules\api\controllers;

use common\models\Bilhete;
use common\models\Compra;
use common\models\Sessao;
use Throwable;
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
        $userId = Yii::$app->user->id;

        $compras = Compra::find()
            ->where(['cliente_id' => $userId])
            ->with(['bilhetes', 'sessao.filme', 'sessao.cinema']) // Eager loading (reduz queries)
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return array_map(fn($compra) =>
             [
                'id' => $compra->id,
                'cliente_id' => $compra->cliente_id,
                'data' => $compra->dataFormatada,
                'total' => $compra->totalEmEuros,
                'estado' => $compra->displayEstado(),

                'filme_id' => $compra->sessao->filme_id,
                'filme_nome' => $compra->sessao->filme->titulo,

                'cinema_id' => $compra->sessao->cinema_id,
                'cinema_nome' => $compra->sessao->cinema->nome,

                'sessao_id' => $compra->sessao->id,
                'sessao_data' => $compra->sessao->dataFormatada,
                'sessao_hora_inicio' => $compra->sessao->horaInicioFormatada,
            ], $compras);
    }

    public function actionView($id)
    {
        $userId = Yii::$app->user->id;

        $compra = Compra::find()
            ->where(['id' => $id, 'cliente_id' => $userId])
            ->with(['bilhetes', 'sessao.filme', 'sessao.cinema', 'sessao.sala']) // Eager loading (reduz queries)
            ->one();

        if (!$compra) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        return [
            'id' => $compra->id,
            'cliente_id' => $compra->cliente_id,
            'data' => $compra->dataFormatada,
            'total' => $compra->totalEmEuros,
            'estado' => $compra->displayEstado(),

            'filme_id' => $compra->sessao->filme_id,
            'filme_nome' => $compra->sessao->filme->titulo,

            'cinema_id' => $compra->sessao->cinema_id,
            'cinema_nome' => $compra->sessao->cinema->nome,

            'sala_id' => $compra->sessao->sala_id,
            'sala_nome' => $compra->sessao->sala->nome,

            'sessao_id' => $compra->sessao->id,
            'sessao_data' => $compra->sessao->dataFormatada,
            'sessao_horario' => $compra->sessao->horario,

            'bilhetes' => array_map(fn($bilhete) =>
                [
                    'id' => $bilhete->id,
                    'lugar' => $bilhete->lugar,
                    'preco' => $bilhete->preco,
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
            throw new BadRequestHttpException("sessao_id, pagamento e lugares são obrigatórios.");
        }

        $sessao = Sessao::findOne($sessaoId);

        if (!$sessao->isEstadoAtiva()) {
            throw new BadRequestHttpException("Sessão inválida.");
        }

        $ocupados = $sessao->getLugaresOcupados();
        $validos = $sessao->sala->getArrayLugares();

        foreach ($lugares as $lugar) {
            if (!in_array($lugar, $validos)) {
                return [
                    'status' => 'error',
                    'message' => "O lugar $lugar não é válido."
                ];
            }
            if (in_array($lugar, $ocupados)) {
                return [
                    'status' => 'error',
                    'message' => "O lugar $lugar já está ocupado."
                ];
            }
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Criar compra
            $compra = new Compra();
            $compra->cliente_id = $userId;
            $compra->sessao_id = $sessaoId;
            $compra->data = date('Y-m-d H:i:s');
            $compra->pagamento = $pagamento;
            $compra->estado = Compra::ESTADO_CONFIRMADA;

            if (!$compra->save()) {
                $transaction->rollBack();
                return ['status' => 'error', 'errors' => $compra->errors];
            }

            // Criar bilhetes
            $bilhetesCriados = [];

            foreach ($lugares as $lugar) {
                $bilhete = new Bilhete();
                $bilhete->compra_id = $compra->id;
                $bilhete->lugar = $lugar;
                $bilhete->preco = $sessao->sala->preco_bilhete;
                $bilhete->codigo = Bilhete::gerarCodigo();
                $bilhete->estado = Bilhete::ESTADO_PENDENTE;

                if (!$bilhete->save()) {
                    $transaction->rollBack();
                    return ['status' => 'error', 'errors' => $bilhete->errors];
                }

                $bilhetesCriados[] = [
                    'id' => $bilhete->id,
                    'codigo' => $bilhete->codigo,
                    'lugar' => $bilhete->lugar,
                    'preco' => $bilhete->preco,
                ];
            }

            $transaction->commit();

            return [
                'status' => 'success',
                'compra_id' => $compra->id,
                'total' => $compra->total,
                'bilhetes' => $bilhetesCriados
            ];
        }
        catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

}