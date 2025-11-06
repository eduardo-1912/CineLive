<?php

namespace backend\controllers;

use Yii;
use common\models\Bilhete;
use backend\models\BilheteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BilheteController implements the CRUD actions for Bilhete model.
 */
class BilheteController extends Controller
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
                        'roles' => ['admin', 'gerente'],
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

    public function actionUpdateLugar($id)
    {
        $model = $this->findModel($id);

        // 🔒 Verificar permissões
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('gerirCompras')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar lugares.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // 🚫 Só pode editar se o bilhete estiver pendente
        if ($model->estado !== Bilhete::ESTADO_PENDENTE) {
            Yii::$app->session->setFlash('warning', 'Apenas bilhetes pendentes podem ser editados.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        $sessao = $model->sessao;

        // 🕒 Impedir alterações se a sessão já começou
        $agora = new \DateTime();
        $dataHoraSessao = new \DateTime($sessao->data . ' ' . $sessao->hora_inicio);

        if ($agora >= $dataHoraSessao) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar lugares de uma sessão já iniciada.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // 🔄 Carregar dados do POST
        if ($model->load(Yii::$app->request->post())) {
            $novoLugar = strtoupper(trim($model->lugar));

            // ✅ Verificar se o lugar já está ocupado (exceto cancelados)
            $lugarOcupado = $sessao->getBilhetes()
                ->andWhere(['lugar' => $novoLugar])
                ->andWhere(['<>', 'estado', Bilhete::ESTADO_CANCELADO])
                ->andWhere(['<>', 'id', $model->id])
                ->exists();

            if ($lugarOcupado) {
                Yii::$app->session->setFlash('error', "O lugar {$novoLugar} já está ocupado nesta sessão.");
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            // 💾 Guardar alterações
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Lugar atualizado com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar o lugar.');
            }
        }

        return $this->redirect(['compra/view', 'id' => $model->compra_id]);
    }




    // MUDAR O ESTADO DO BILHETE
    public function actionChangeStatus($id, $estado)
    {
        $model = $this->findModel($id);

        // 🔒 Permissões
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('gerirCompras')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado de bilhetes.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // ⚠️ Estados válidos
        $estadosValidos = array_keys(Bilhete::optsEstado());
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // 🚫 Se já estiver no estado pretendido
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', 'O bilhete já se encontra neste estado.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        $sessao = $model->sessao;

        // 🚫 Impedir ativação de bilhete se a compra estiver cancelada
        if (in_array($estado, [Bilhete::ESTADO_PENDENTE, Bilhete::ESTADO_CONFIRMADO]) &&
            $model->compra->estado === \common\models\Compra::ESTADO_CANCELADA) {
            Yii::$app->session->setFlash('error', 'Não é possível reativar ou confirmar bilhetes de uma compra cancelada.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // ⚙️ Se for descancelar (cancelado → pendente/confirmado)
        if ($model->estado === Bilhete::ESTADO_CANCELADO && $estado !== Bilhete::ESTADO_CANCELADO) {
            $lugaresDisponiveis = $sessao->getNumeroLugaresDisponiveis();

            if ($lugaresDisponiveis <= 0) {
                Yii::$app->session->setFlash('error', 'Não é possível reativar o bilhete. Não há lugares disponíveis.');
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            // ⚠️ Libertar ou manter lugar antigo se ainda estiver livre
            $lugarOcupado = $sessao->getBilhetes()
                ->andWhere(['lugar' => $model->lugar])
                ->andWhere(['<>', 'estado', Bilhete::ESTADO_CANCELADO])
                ->andWhere(['<>', 'id', $model->id])
                ->exists();

            if ($lugarOcupado) {
                $model->lugar = null; // libertar se já estiver ocupado
            }

        }

        // 🚫 Impedir confirmação sem lugar
        if ($estado === Bilhete::ESTADO_CONFIRMADO && empty($model->lugar)) {
            Yii::$app->session->setFlash('warning', 'Não é possível confirmar um bilhete sem lugar atribuído.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // 📝 Alterar estado
        $model->estado = $estado;

        if ($model->save(false, ['estado', 'lugar'])) {
            Yii::$app->session->setFlash('success', 'Estado do bilhete atualizado com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao atualizar o estado do bilhete.');
        }

        return $this->redirect(['compra/view', 'id' => $model->compra_id]);
    }





    /**
     * Finds the Bilhete model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Bilhete the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bilhete::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
