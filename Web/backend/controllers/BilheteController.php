<?php

namespace backend\controllers;

use Yii;
use common\models\Bilhete;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class BilheteController extends Controller
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
                        'roles' => ['admin', 'gerente', 'funcionario'],
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

    public function actionUpdateLugar($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $confirmarBilhetes = $currentUser->can('confirmarBilhetes');
        $confirmarBilhetesCinema = $currentUser->can('confirmarBilhetesCinema', ['model' => $model->compra->sessao->cinema]);

        if (!$confirmarBilhetes && !$confirmarBilhetesCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar este bilhete.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->compra->isEstadoCancelada()) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar bilhetes de uma compra cancelada.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if (!$model->isEstadoPendente()) {
            Yii::$app->session->setFlash('warning', 'Apenas bilhetes pendentes podem ser editados.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->compra->sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não pode editar bilhetes cuja sessão já terminou.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->compra->isEstadoCancelada()) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar bilhetes de uma compra cancelada.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Normalizar
            $novoLugar = strtoupper(trim($model->lugar));

            $lugaresSala = $model->compra->sessao->sala->getLugares();
            $lugaresOcupados = $sessao->lugaresOcupados ?? [];

            // Verificar se novo lugar é válido
            $lugarValido = in_array($novoLugar, $lugaresSala) && !in_array($novoLugar, $lugaresOcupados);

            if (!$lugarValido) {
                Yii::$app->session->setFlash('error', "O lugar {$novoLugar} não está disponível ou não existe nesta sala.");
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            $model->lugar = $novoLugar;

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Lugar atualizado com sucesso.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar o lugar.');
            }
        }

        return $this->redirect(['compra/view', 'id' => $model->compra_id]);
    }

    public function actionChangeStatus($id, $estado)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $confirmarBilhetes = $currentUser->can('confirmarBilhetes');
        $confirmarBilhetesCinema = $currentUser->can('confirmarBilhetesCinema', ['model' => $model->compra->sessao->cinema]);

        if (!$confirmarBilhetes && !$confirmarBilhetesCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar este bilhete.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->estado === $estado) {
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }


        if ($model->compra->isEstadoCancelada()) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar bilhetes de uma compra cancelada.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->compra->sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não pode alterar o estado de bilhetes cuja sessão já terminou.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // SE FOR DESCANCELAR --> VER SE O LUGAR AINDA ESTÁ DISPONÍVEL
        if ($model->isEstadoCancelado() && $estado !== $model::ESTADO_CANCELADO) {

            $lugaresDisponiveis = $model->compra->sessao->isEstadoEsgotada();

            if ($lugaresDisponiveis <= 0) {
                Yii::$app->session->setFlash('error', 'Não é possível reativar o bilhete pois a sessão está esgotada.');
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            // Ver se o lugar foi ocupado
            $lugarOcupado = $model->compra->sessao->getBilhetes()
                ->andWhere(['lugar' => $model->lugar])
                ->andWhere(['<>', 'estado', Bilhete::ESTADO_CANCELADO])
                ->andWhere(['<>', 'id', $model->id])
                ->exists();

            // Se lugar estiver ocupado --> null
            if ($lugarOcupado) {
                $model->lugar = null;
            }

        }

        if ($estado === $model::ESTADO_CONFIRMADO && empty($model->lugar)) {
            Yii::$app->session->setFlash('warning', 'Não é possível confirmar um bilhete sem lugar atribuído.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // Alterar o estado
        $model->estado = $estado;

        if ($model->save(false, ['estado', 'lugar'])) {
            Yii::$app->session->setFlash('success', 'Estado do bilhete atualizado com sucesso.');
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao atualizar o estado do bilhete.');
        }

        return $this->redirect(['compra/view', 'id' => $model->compra_id]);
    }

    public function actionValidate()
    {
        $currentUser = Yii::$app->user;
        $codigo = Yii::$app->request->post('codigo');

        if (empty($codigo)) {
            Yii::$app->session->setFlash('error', 'Por favor, insira um código de bilhete.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        $bilhete = Bilhete::findOne(['codigo' => $codigo]);

        $confirmarBilhetes = $currentUser->can('confirmarBilhetes');
        $confirmarBilhetesCinema = $currentUser->can('confirmarBilhetesCinema', ['model' => $bilhete->compra->sessao->cinema]);

        if (!$confirmarBilhetes && !$confirmarBilhetesCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para confirmar este bilhete.');
            return $this->goHome();
        }

        if (empty($codigo)) {
            Yii::$app->session->setFlash('error', 'Por favor, insira um código de bilhete.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // Ver se é para confirmar todos os bilhetes da compra
        $confirmarTodos = Yii::$app->request->post('confirmar_todos');

        // Ver se o bilhete existe
        if (!$bilhete) {
            Yii::$app->session->setFlash('error', 'Código de bilhete inválido.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // Se o bilhete já está confirmado --> voltar
        if ($bilhete->isEstadoConfirmado()) {
            Yii::$app->session->setFlash('info', 'Este bilhete já foi confirmado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // Confirmar todos os bilhetes
        if ($confirmarTodos && $bilhete->compra_id) {
            foreach ($bilhete->compra->bilhetes as $bilhete) {
                $bilhete->estado = $bilhete::ESTADO_CONFIRMADO;
                $bilhete->save(false, ['estado']);
            }
        }

        // Apenas confirmar o bilhete inserido
        $bilhete->estado = $bilhete::ESTADO_CONFIRMADO;
        $bilhete->save(false, ['estado']);

        Yii::$app->session->setFlash('success', "Bilhete(s) confirmado(s) com sucesso!");
        return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
    }

    protected function findModel($id)
    {
        if (($model = Bilhete::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
