<?php

namespace backend\modules\api\controllers;

use common\helpers\Formatter;
use common\models\Bilhete;
use common\models\Cinema;
use common\models\Compra;
use common\models\Filme;
use common\models\Sessao;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
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

    // region CRUD
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
            'pagamento' => $compra->displayPagamento(),
            'filme_id' => $compra->sessao->filme_id,
            'filme_titulo' => $compra->sessao->filme->titulo,
            'cinema_id' => $compra->sessao->cinema_id,
            'cinema_nome' => $compra->sessao->cinema->nome,
            'sala_id' => $compra->sessao->sala_id,
            'sala_nome' => $compra->sessao->sala->nome,
            'sessao_id' => $compra->sessao->id,
            'sessao_data' => Formatter::data($compra->sessao->data),
            'sessao_hora_inicio' => Formatter::hora($compra->sessao->hora_inicio),
            'sessao_hora_fim' => Formatter::hora($compra->sessao->hora_fim),
            'lugares' => $compra->lugares,
        ], $compras);
    }

    public function actionView($id)
    {
        $compra = Compra::findOne($id);

        if (!$compra || $compra->cliente_id != Yii::$app->user->id) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        return [
            'id' => $compra->id,
            'cliente_id' => $compra->cliente_id,
            'data' => Formatter::data($compra->data),
            'total' => Formatter::preco($compra->total),
            'estado' => $compra->displayEstado(),
            'pagamento' => $compra->displayPagamento(),
            'filme_id' => $compra->sessao->filme_id,
            'filme_titulo' => $compra->sessao->filme->titulo,
            'cinema_id' => $compra->sessao->cinema_id,
            'cinema_nome' => $compra->sessao->cinema->nome,
            'sala_id' => $compra->sessao->sala_id,
            'sala_nome' => $compra->sessao->sala->nome,
            'sessao_id' => $compra->sessao->id,
            'sessao_data' => Formatter::data($compra->sessao->data),
            'sessao_hora_inicio' => Formatter::hora($compra->sessao->hora_inicio),
            'sessao_hora_fim' => Formatter::hora($compra->sessao->hora_fim),
            'bilhetes' => array_map(fn($bilhete) => [
                'id' => $bilhete->id,
                'codigo' => $bilhete->codigo,
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

        $campos = ['sessao_id', 'pagamento', 'lugares'];
        foreach ($campos as $campo) {
            // Criar variável com o nome do campo
            $$campo = $body[$campo] ?? null;

            if (empty($$campo)) {
                throw new BadRequestHttpException("O campo '$campo' é obrigatório.");
            }
        }

        $sessao = Sessao::findOne($sessao_id);

        if (!$sessao || !$sessao->isEstadoAtiva()) {
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

        // Criar compra
        $compra = new Compra();
        $compra->cliente_id = $userId;
        $compra->sessao_id = $sessao_id;
        $compra->data = date('Y-m-d H:i:s');
        $compra->pagamento = $pagamento;
        $compra->estado = $compra::ESTADO_CONFIRMADA;

        if (!$compra->save()) {
            return [
                'status' => 'error',
                'errors' => $compra->errors
            ];
        }

        // Criar bilhetes
        $bilhetes = [];
        foreach ($lugares as $lugar) {
            $bilhete = new Bilhete();
            $bilhete->compra_id = $compra->id;
            $bilhete->lugar = $lugar;
            $bilhete->preco = $sessao->sala->preco_bilhete;
            $bilhete->codigo = $bilhete::gerarCodigo();
            $bilhete->estado = $bilhete::ESTADO_PENDENTE;

            if (!$bilhete->save()) {
                // Eliminar compra e bilhetes anteriores
                Bilhete::deleteAll(['compra_id' => $compra->id]);
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
        ];
    }

    public function actionDelete($id)
    {
        $compra = Compra::findOne($id);

        if (!$compra) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        if (!$compra->isEstadoCancelada()) {
            throw new ForbiddenHttpException("Apenas pode eliminar compras canceladas.");
        }

        if (!$compra->delete()) {
            return [
                'status' => 'error',
                'message' => 'Erro ao eliminar a compra.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Compra eliminada com sucesso.'
        ];
    }
    // endregion

    // region ExtraPatterns
    // Todas as compras com bilhetes
    public function actionComBilhetes()
    {
        $user = Yii::$app->user->identity;
        $compras = $user->getCompras()->orderBy(['id' => SORT_DESC])->all();

        return array_map(fn($compra) => [
            'id' => $compra->id,
            'cliente_id' => $compra->cliente_id,
            'data' => Formatter::data($compra->data),
            'total' => Formatter::preco($compra->total),
            'estado' => $compra->displayEstado(),
            'pagamento' => $compra->displayPagamento(),
            'filme_id' => $compra->sessao->filme_id,
            'filme_titulo' => $compra->sessao->filme->titulo,
            'cinema_id' => $compra->sessao->cinema_id,
            'cinema_nome' => $compra->sessao->cinema->nome,
            'sala_id' => $compra->sessao->sala_id,
            'sala_nome' => $compra->sessao->sala->nome,
            'sessao_id' => $compra->sessao->id,
            'sessao_data' => Formatter::data($compra->sessao->data),
            'sessao_hora_inicio' => Formatter::hora($compra->sessao->hora_inicio),
            'sessao_hora_fim' => Formatter::hora($compra->sessao->hora_fim),
            'lugares' => $compra->lugares,
            'bilhetes' => array_map(fn($bilhete) => [
                'id' => $bilhete->id,
                'codigo' => $bilhete->codigo,
                'lugar' => $bilhete->lugar,
                'preco' => Formatter::preco($bilhete->preco),
                'estado' => $bilhete->displayEstado(),
            ], $compra->bilhetes)
        ], $compras);
    }

    // Bilhetes de uma compra
    public function actionBilhetes($id)
    {
        $compra = Compra::findOne($id);

        if (!$compra || $compra->cliente_id != Yii::$app->user->id) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        return array_map(fn($bilhete) => [
            'id' => $bilhete->id,
            'codigo' => $bilhete->codigo,
            'lugar' => $bilhete->lugar,
            'preco' => Formatter::preco($bilhete->preco),
            'estado' => $bilhete->displayEstado(),
        ], $compra->bilhetes);
    }

    // Total de todas as compras do cliente
    public function actionTotal()
    {
        $total = 0;
        foreach (Yii::$app->user->identity->compras as $compra) {
            $total += array_sum(array_column($compra->bilhetes, 'preco'));
        }

        return $total;
    }

    // Contar compras do cliente
    public function actionCount() {
        return Yii::$app->user->identity->getCompras()->count();
    }

    // Contar de bilhetes de uma compra
    public function actionCountBilhetes($id) {
        $compra = Compra::findOne($id);

        if (!$compra || $compra->cliente_id != Yii::$app->user->id) {
            throw new NotFoundHttpException("Compra não encontrada.");
        }

        return count($compra->bilhetes);
    }

    // Contar compras do cliente por filme
    public function actionCountPorFilme($filme_id)
    {
        $user = Yii::$app->user->identity;
        $count = $user->getCompras()
            ->joinWith('sessao')
            ->where(['sessao.filme_id' => $filme_id])
            ->count();

        return [
            'filme_id' => $filme_id,
            'filme_titulo' => Filme::findOne($filme_id)->titulo,
            'count' => $count,
        ];
    }

    // Contar compras do cliente por cinema
    public function actionCountPorCinema($cinema_id)
    {
        $user = Yii::$app->user->identity;
        $count = $user->getCompras()
            ->joinWith('sessao')
            ->where(['cinema_id' => $cinema_id])
            ->count();

        return [
            'cinema_id' => $cinema_id,
            'cinema_nome' => Cinema::findOne($cinema_id)->nome,
            'count' => $count,
        ];
    }
    // endregion
}