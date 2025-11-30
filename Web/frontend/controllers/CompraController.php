<?php

namespace frontend\controllers;

use common\components\Formatter;
use common\models\Bilhete;
use common\models\Compra;
use common\models\Sessao;
use frontend\helpers\CookieHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CompraController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['cliente'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $compras = Yii::$app->user->identity->getCompras()->orderBy(['id' => SORT_DESC])->all();

        return $this->render('index', [
            'compras' => $compras,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('verCompras', ['model' => $model])) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver esta compra.');
            return $this->redirect(['compra/index']);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate($sessao_id)
    {
        if (!Yii::$app->user->can('criarCompra')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar compras.');
            return $this->redirect(['filme/index']);
        }

        $sessao = Sessao::findOne($sessao_id);

        if (!$sessao || !$sessao->isEstadoAtiva()) {
            Yii::$app->session->setFlash('error', "Não é possível comprar bilhetes para esta sessão.");
            return $this->redirect(Yii::$app->request->referrer ?: ['filme/index']);
        }

        // Obter lugares possíveis e ocupados
        $lugaresPossiveis = $sessao->sala->lugares;
        $lugaresOcupados = $sessao->lugaresOcupados;

        // Ler lugares do URL
        $lugaresSelecionados = array_filter(explode(',', Yii::$app->request->get('lugares', '')));

        // Validar lugares
        $lugaresValidos = [];
        foreach ($lugaresSelecionados as $lugar) {
            if (in_array($lugar, $lugaresPossiveis) && !in_array($lugar, $lugaresOcupados)) {
                $lugaresValidos[] = $lugar;
            }
        }

        if ($lugaresSelecionados !== $lugaresValidos) {
            return $this->redirect([
                'compra/create',
                'sessao_id' => $sessao_id,
                'lugares' => implode(',', $lugaresValidos)
            ]);
        }

        // Atualizar resumo
        $total = $lugaresSelecionados ? Formatter::preco(count($lugaresSelecionados) * $sessao->sala->preco_bilhete) : '-';
        $lugares = $lugaresSelecionados ? implode(', ', $lugaresSelecionados) : '-';

        // Criar mapa de lugares
        $mapaLugares = [];
        for ($fila = 1; $fila <= $sessao->sala->num_filas; $fila++) {
            for ($coluna = 1; $coluna <= $sessao->sala->num_colunas; $coluna++) {

                // Criar lugar
                $lugar = chr(64 + $fila) . $coluna;

                // Verificar se está ocupado ou selecionado
                $ocupado = in_array($lugar, $lugaresOcupados);
                $selecionado = in_array($lugar, $lugaresSelecionados);

                // Nova seleção de lugares
                $novaSelecao = $lugaresSelecionados;

                // Remover lugar
                if ($selecionado) {
                    $novaSelecao = array_diff($novaSelecao, [$lugar]);
                }
                // Adicionar lugar
                else {
                    $novaSelecao[] = $lugar;
                }

                // URL do lugar
                $mapaLugares[$fila][$coluna] = [
                    'label' => $coluna,
                    'url' => Url::to([
                        'compra/create', 'sessao_id' => $sessao->id, 'lugares' => implode(',', $novaSelecao)
                    ]),
                    'ocupado' => $ocupado,
                    'selecionado' => $selecionado,
                ];
            }
        }

        return $this->render('create', [
            'sessao' => $sessao,
            'lugaresSelecionados' => $lugaresSelecionados,
            'mapaLugares' => $mapaLugares,
            'lugares' => $lugares,
            'total' => $total,
        ]);
    }

    public function actionPay()
    {
        if (!Yii::$app->user->can('criarCompra')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar compras.');
            return $this->redirect(['filme/index']);
        }

        // Obter dados
        $sessaoId = Yii::$app->request->post('sessao_id');
        $lugares = explode(',', Yii::$app->request->post('lugares'));
        $metodo = Yii::$app->request->post('metodo');

        // Obter a sessão
        $sessao = Sessao::findOne($sessaoId);

        if (!$sessao || !$sessao->isEstadoAtiva()) {
            Yii::$app->session->setFlash('error', "Não é possível comprar bilhetes para esta sessão.");
            return $this->redirect(Yii::$app->request->referrer ?: ['filme/index']);
        }

        if (empty($lugares) || !$metodo) {
            Yii::$app->session->setFlash('error', 'Faltam dados obrigatórios.');
            return $this->redirect('compra/create', ['sessao_id' => $sessaoId]);
        }

        // Criar a compra
        $compra = new Compra();
        $compra->cliente_id = Yii::$app->user->id;
        $compra->sessao_id = $sessao->id;
        $compra->data = date('Y-m-d H:i:s');
        $compra->pagamento = $metodo;
        $compra->estado = Compra::ESTADO_CONFIRMADA;

        if (!$compra->save()) {
            Yii::$app->session->setFlash('error', 'Erro ao criar a compra.');
            return $this->redirect(['compra/create', 'sessao_id' => $sessaoId]);
        }

        // Criar bilhetes
        foreach ($lugares as $lugar) {

            $bilhete = new Bilhete();
            $bilhete->compra_id = $compra->id;
            $bilhete->lugar = $lugar;
            $bilhete->preco = $sessao->sala->preco_bilhete;
            $bilhete->codigo = Bilhete::gerarCodigo();
            $bilhete->estado = Bilhete::ESTADO_PENDENTE;

            if (!$bilhete->save()) {
                // Eliminar bilhetes anteriores
                Bilhete::deleteAll(['compra_id' => $compra->id]);

                // Eliminar a compra
                $compra->delete();

                Yii::$app->session->setFlash('error', 'Erro ao criar os bilhetes.');
                return $this->redirect(['compra/create', 'sessao_id' => $sessaoId]);
            }
        }

        // Atualizar o cookie de cinema
        CookieHelper::set('cinema_id', $sessao->cinema->id, 365);

        Yii::$app->session->setFlash('success', 'Compra realizada com sucesso.');
        return $this->redirect(['compra/view', 'id' => $compra->id]);
    }

    protected function findModel($id)
    {
        if (($model = Compra::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}