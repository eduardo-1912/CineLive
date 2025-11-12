<?php

namespace frontend\controllers;

use common\models\Bilhete;
use common\models\Compra;
use common\models\Sessao;
use DateTime;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

class CompraController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['cliente'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    // ESCOLHER LUGARES E PAGAMENTO
    public function actionCreate($sessao_id)
    {
        // OBTER A SESSÃO
        $sessao = Sessao::findOne($sessao_id);

        if (!$sessao) {
            return $this->redirect(Yii::$app->request->referrer ?: ['filme/index']);
        }

        // SE A SESSÃO ESTÁ ESGOTADA, TERMINADA OU A DECORRER --> SEM ACESSO
        if ($sessao->isEstadoEsgotada() || $sessao->isEstadoTerminada() || $sessao->isEstadoADecorrer()) {
            Yii::$app->session->setFlash('error', "Já não é possível comprar bilhetes para esta sessão.");
            return $this->redirect(Yii::$app->request->referrer ?: ['filme/index']);
        }

        // CALCULAR SE A SESSÃO ESTÁ PERTO DE COMEÇAR
        $now = new DateTime();
        $inicioSessao = new DateTime($sessao->data . ' ' . $sessao->hora_inicio);

        $segundosRestantes = $inicioSessao->getTimestamp() - $now->getTimestamp();
        $minutosRestantes = floor($segundosRestantes / 60);

        // AVISO SE COMEÇA DENTRO DE 60 MINUTOS, MAS AINDA NÃO COMEÇOU
        if ($segundosRestantes > 0 && $minutosRestantes <= 60) {
            Yii::$app->session->setFlash('warning', "A sessão começa dentro de {$minutosRestantes} minutos!");
        }

        $sala = $sessao->sala;
        $lugaresSala = $sala->getArrayLugares();
        $lugaresOcupados = $sessao->lugaresOcupados ?? [];

        // LER LUGARES DO URL
        $lugaresSelecionados = Yii::$app->request->get('lugares', '');
        $lugaresSelecionados = array_filter(explode(',', $lugaresSelecionados));

        // VALIDAR LUGARES
        $lugaresValidos = [];
        foreach ($lugaresSelecionados as $lugar) {
            // SE LUGAR EXISTE NA SALA E NÃO ESTÁ OCUPADO --> É VÁLIDO
            if (in_array($lugar, $lugaresSala) && !in_array($lugar, $lugaresOcupados)) {
                $lugaresValidos[] = $lugar;
            }
        }

        // SE OS LUGARES FOREM DIFERENTES --> REDIRECIONAR COM LUGARES VÁLIDOS
        if ($lugaresSelecionados !== $lugaresValidos) {
            return $this->redirect([
                'compra/create',
                'sessao_id' => $sessao_id,
                'lugares' => implode(',', $lugaresValidos)
            ]);
        }


        // CALCULAR TOTAL DA COMPRA
        $total = 0;
        if (!empty($lugaresSelecionados)) {
            $total = count($lugaresSelecionados) * (float)$sessao->sala->preco_bilhete;
        }

        return $this->render('create', [
            'sessao' => $sessao,
            'lugaresSelecionados' => $lugaresSelecionados,
            'total' => $total,
        ]);
    }

    // CRIAR COMPRA E BILHETES
    public function actionPay()
    {
        $request = Yii::$app->request;

        // OBTER DADOS
        $sessaoId = $request->post('sessao_id');
        $lugares = array_filter(explode(',', (string)$request->post('lugares')));
        $metodo = $request->post('metodo');

        // OBTER A SESSÃO
        $sessao = Sessao::findOne($sessaoId);

        // SE A SESSÃO NÃO EXISTE --> VOLTAR
        if (!$sessao) {
            return $this->redirect(Yii::$app->request->referrer ?: ['filme/index']);
        }

        // SE A SESSÃO ESTÁ ESGOTADA, TERMINADA OU A DECORRER --> SEM ACESSO
        if ($sessao->isEstadoEsgotada() || $sessao->isEstadoTerminada() || $sessao->isEstadoADecorrer()) {
            Yii::$app->session->setFlash('error', "Já não é possível comprar bilhetes para esta sessão.");
            return $this->redirect(Yii::$app->request->referrer ?: ['filme/index']);
        }

        // SE ALGUM DOS DADOS NÃO FOI PASSADO --> VOLTAR
        if (empty($lugares) || !$metodo) {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar a compra.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/create', 'sessao_id' => $sessaoId]);

        }

        // INICIAR TRANSACTION (TER A CERTEZA QUE NENHUMA COMPRA É CRIADA SEM BILHETES)
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // CRIAR A COMPRA
            $compra = new Compra();
            $compra->cliente_id = Yii::$app->user->id;
            $compra->sessao_id = $sessao->id;
            $compra->data = date('Y-m-d H:i:s');
            $compra->pagamento = $metodo;
            $compra->estado = 'confirmada';

            // GUARDAR
            if (!$compra->save()) {
                throw new Exception('Ocorreu um erro ao guardar a compra: ' . json_encode($compra->getErrors()));
            }

            // CRIAR BILHETES
            foreach ($lugares as $lugar) {

                // GERAR CÓDIGO ÚNICO
                do { $codigo = strtoupper(Yii::$app->security->generateRandomString(6)); }
                while (Bilhete::find()->where(['codigo' => $codigo])->exists());

                // CRIAR BILHETE
                $bilhete = new Bilhete();
                $bilhete->compra_id = $compra->id;
                $bilhete->lugar = $lugar;
                $bilhete->preco = $sessao->sala->preco_bilhete;
                $bilhete->codigo = $codigo;
                $bilhete->estado = 'pendente';

                // GUARDAR
                if (!$bilhete->save()) {
                    throw new Exception('Ocorreu um erro ao guardar o bilhete: ' . json_encode($bilhete->getErrors()));
                }
            }

            // DAR COMMIT NA TRANSACTION
            $transaction->commit();

            Yii::$app->session->setFlash('success', 'Compra realizada com sucesso.');
            return $this->redirect(['compra/view', 'id' => $compra->id]);
        }
        catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar a compra.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/create', 'sessao_id' => $sessaoId]);
        }
    }

    protected function findModel($id)
    {
        if (($model = Compra::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}