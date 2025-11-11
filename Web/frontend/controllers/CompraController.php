<?php

namespace frontend\controllers;

use common\models\Compra;
use common\models\Sessao;
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
                        'roles' => ['@'],
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

    public function actionCreate($sessao_id)
    {
        // OBTER A SESSÃO
        $sessao = Sessao::findOne($sessao_id);

        // LER LUGARES DO URL
        $lugaresSelecionados = Yii::$app->request->get('lugares', '');
        $lugaresSelecionados = array_filter(explode(',', $lugaresSelecionados));

        // CALCULAR TOTAL
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

    public function actionPay()
    {
        $request = Yii::$app->request;

        $sessaoId = $request->post('sessao_id');
        $lugares = explode(',', $request->post('lugares'));
        $metodo = $request->post('metodo');

        $sessao = \common\models\Sessao::findOne($sessaoId);

        if (!$sessao || empty($lugares) || !$metodo) {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro.');
            return $this->redirect(['filme/index']);
        }

        $userId = Yii::$app->user->id ?? null;
        $total = count($lugares) * (float)$sessao->sala->preco_bilhete;

        // --- Criar a compra ---
        $compra = new \common\models\Compra();
        $compra->cliente_id = $userId;
        $compra->sessao_id = $sessao->id;
        $compra->data = date('Y-m-d H:i:s');
        $compra->pagamento = $metodo;
        $compra->estado = 'confirmada';

        if (!$compra->save()) {
            Yii::$app->session->setFlash('error', 'Erro ao criar a compra.');
            return $this->redirect(['compra/create', 'sessao_id' => $sessaoId]);
        }

        // --- Criar bilhetes ---
        foreach ($lugares as $lugar) {

            do {
                $codigo = strtoupper(Yii::$app->security->generateRandomString(6));
            } while (\common\models\Bilhete::find()->where(['codigo' => $codigo])->exists());



            $bilhete = new \common\models\Bilhete();
            $bilhete->compra_id = $compra->id;
            $bilhete->lugar = $lugar;
            $bilhete->preco = $sessao->sala->preco_bilhete;
            $bilhete->codigo = $codigo;
            $bilhete->estado = 'pendente';

            if (!$bilhete->save()) {
                Yii::$app->session->setFlash('error', 'Erro ao criar o bilhete para o lugar ' . $lugar);
                return $this->redirect(['compra/create', 'sessao_id' => $sessaoId]);
            }
        }

        // --- Redirecionar com sucesso ---
        Yii::$app->session->setFlash('success', 'Compra realizada com sucesso!');
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