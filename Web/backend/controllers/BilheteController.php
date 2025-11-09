<?php

namespace backend\controllers;

use common\models\Compra;
use Yii;
use common\models\Bilhete;
use backend\models\BilheteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

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
                        'roles' => ['admin', 'gerente', 'funcionario'],
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

    // ADMIN --> ATUALIZA O LUGAR DE BILHETES DE QUALQUER COMPRA
    // GERENTE/FUNCIONÁRIO --> ATUALIZA O LUGAR DE BILHETES DO SEU CINEMA
    public function actionUpdateLugar($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER BILHETE
        $model = $this->findModel($id);

        // VERIFICAR PERMISSÃO
        if (!Yii::$app->user->can('validarBilhetes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar bilhetes.');
            return $this->redirect(['compra/index']);
        }

        // OBTER SESSÃO DA COMRA
        $sessao = $model->compra->sessao;

        // OBTER CINEMA DA COMPRA
        $cinemaId = $sessao->cinema_id ?? null;

        // GERENTE/FUNCIONÁRIO --> SÓ PODEM CONFIRMAR COMPRAS DO SEU CINEMA
        if (!$currentUser->can('admin')) {

            // OBTER CINEMA DO USER ATUAL
            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            if ($userCinemaId === null || $userCinemaId != $cinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para editar bilhetes de outro cinema.');
                return $this->redirect(['index']);
            }
        }

        // SÓ PODE EDITAR BILHETES PENDENTES
        if ($model->estado !== Bilhete::ESTADO_PENDENTE) {
            Yii::$app->session->setFlash('warning', 'Apenas bilhetes pendentes podem ser editados.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // SE A SESSÃO JÁ TERMINOU --> NÃO DEIXAR ALTERAR O LUGAR
        if ($sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não pode editar bilhetes cuja sessão já terminou.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        if ($model->load(Yii::$app->request->post())) {

            // OBTER NOVO LUGAR
            $model->lugar = strtoupper(trim($model->lugar));
            $novoLugar = $model->lugar;

            // OBTER LUGARES VÁLIDOS
            $lugaresValidos = $sessao->sala->getLugaresValidos();

            // SE LUGAR NÀO FOR VÁLIDO --> MENSAGEM DE ERRO
            if (!in_array($novoLugar, $lugaresValidos)) {
                Yii::$app->session->setFlash('error', "O lugar {$novoLugar} não existe nesta sala.");
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            // VERIFICAR SE O LUGAR ESTÁ OCUPADO
            $lugarOcupado = $sessao->getBilhetes()
                ->andWhere(['lugar' => $novoLugar])
                ->andWhere(['<>', 'estado', Bilhete::ESTADO_CANCELADO])
                ->andWhere(['<>', 'id', $model->id])
                ->exists();

            // SE O LUGAR ESTIVER OCUPADO --> MENSAGEM DE RRO
            if ($lugarOcupado) {
                Yii::$app->session->setFlash('error', "O lugar {$novoLugar} já está ocupado nesta sessão.");
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            // GUARDAR
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Lugar atualizado com sucesso.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar o lugar.');
            }
        }

        return $this->redirect(['compra/view', 'id' => $model->compra_id]);
    }


    // ADMIN/GERENTE/FUNCIONÁRIO --> MUDA O ESTADO DOS BILHETES
    // GERENTE/FUNCIONÁRIO --> MUDA O ESTADO DOS BILHETES DO SEU CINEMA
    public function actionChangeStatus($id, $estado)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER BILHETE
        $model = $this->findModel($id);

        // VERIFICAR PERMISSÃO
        if (!Yii::$app->user->can('validarBilhetes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado de bilhetes.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // VERIFICAR CINEMA (GERENTE/FUNCIONÁRIO --> SÓ O SEU CINEMA)
        if (!$currentUser->can('admin')) {

            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            if ($userCinemaId === null || $userCinemaId != $model->compra->sessao->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para alterar bilhetes de outro cinema.');
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }
        }

        // VERIFICAR QUE O ESTADO É VÁLIDO
        $estadosValidos = array_keys(Bilhete::optsEstado());
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO --> VOLTAR
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', 'O bilhete já se encontra neste estado.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // OBTER A SESSÃO
        $sessao = $model->compra->sessao;

        // IMPEDIR ATIVAÇÃO SE A COMPRA ESTIVER CANCELADA
        if (($estado === Bilhete::ESTADO_PENDENTE || $estado === Bilhete::ESTADO_CONFIRMADO) && $model->compra->estado === Compra::ESTADO_CANCELADA) {
            Yii::$app->session->setFlash('error', 'Não é possível reativar ou confirmar bilhetes de uma compra cancelada.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // SE A SESSÃO JÁ TERMINOU --> NÃO DEIXAR ALTERAR
        if ($sessao->isEstadoTerminada()) {
            Yii::$app->session->setFlash('error', 'Não pode alterar o estado de bilhetes cuja sessão já terminou.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // SE FOR DESCANCELAR --> VER SE O LUGAR AINDA ESTÁ DISPONÍVEL
        if ($model->estado === Bilhete::ESTADO_CANCELADO && $estado !== Bilhete::ESTADO_CANCELADO) {

            $lugaresDisponiveis = $sessao->getNumeroLugaresDisponiveis();

            // SE NÃO EXISTIREM LUGARES DISPONÍVEIS --> NÃO DEIXAR RE-ATIVAR BILHETE
            if ($lugaresDisponiveis <= 0) {
                Yii::$app->session->setFlash('error', 'Não é possível reativar o bilhete. Não há lugares disponíveis.');
                return $this->redirect(['compra/view', 'id' => $model->compra_id]);
            }

            // VER SE O LUGAR FOI OCUPADO POR OUTRO BILHETE
            $lugarOcupado = $sessao->getBilhetes()
                ->andWhere(['lugar' => $model->lugar])
                ->andWhere(['<>', 'estado', Bilhete::ESTADO_CANCELADO])
                ->andWhere(['<>', 'id', $model->id])
                ->exists();

            // SE O LUGAR ESTIVER OCUPADO --> REMOVER O LUGAR ANTIGO
            if ($lugarOcupado) {
                $model->lugar = null;
            }

        }

        // IMPEDIR CONFIRMAR BILHETE SEM LUGAR
        if ($estado === Bilhete::ESTADO_CONFIRMADO && empty($model->lugar)) {
            Yii::$app->session->setFlash('warning', 'Não é possível confirmar um bilhete sem lugar atribuído.');
            return $this->redirect(['compra/view', 'id' => $model->compra_id]);
        }

        // ALTERAR O ESTADO
        $model->estado = $estado;

        if ($model->save(false, ['estado', 'lugar'])) {
            Yii::$app->session->setFlash('success', 'Estado do bilhete atualizado com sucesso.');
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao atualizar o estado do bilhete.');
        }

        return $this->redirect(['compra/view', 'id' => $model->compra_id]);
    }

    // ADMIN --> VALIDA BILHETES DE QUALQUER CINEMA
    // GERENTE/FUNCIONÁRIO --> VALIDA BILHETES DO SEU CINEMA
    public function actionValidate()
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÃO
        if (!Yii::$app->user->can('validarBilhetes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para validar bilhetes.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // OBTER CÓDIGO
        $codigo = Yii::$app->request->post('codigo');

        // SE USAR MARCOU CHECKBOX DE CONFIRMAR TODOS OS BILHETES DA MESMA COMPRA
        $confirmarTodos = Yii::$app->request->post('confirmar_todos');

        // SE NENHUM CÓDIGO FOI PASSADO --> MENSAGEM DE ERRO
        if (empty($codigo)) {
            Yii::$app->session->setFlash('error', 'Por favor, insira um código de bilhete.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // OBTER BILHETE
        $bilhete = Bilhete::findOne(['codigo' => $codigo]);

        // SE BILHETE NÃO EXISTIR --> MENSAGEM DE ERRO
        if (!$bilhete) {
            Yii::$app->session->setFlash('error', 'Código de bilhete inválido.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // VERIFICAR CINEMA (GERENTE/FUNCIONÁRIO --> SÓ O SEU CINEMA)
        if (!$currentUser->can('admin')) {

            // OBTER CINEMA DO USER ATUAL
            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            if ($userCinemaId === null || $userCinemaId != $bilhete->compra->sessao->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para validar bilhetes de outro cinema.');
                return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
            }
        }

        // SE O BILHETE JÁ ESTIVER CONFIRMADO --> VOLTAR
        if ($bilhete->estado === $bilhete::ESTADO_CONFIRMADO) {
            Yii::$app->session->setFlash('info', 'Este bilhete já foi confirmado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['compra/index']);
        }

        // CONFIRMAR BILHETE INDIVIDUAL
        $bilhete->estado = $bilhete::ESTADO_CONFIRMADO;
        $bilhete->save(false, ['estado']);

        $confirmados = 1;

        // SE CHECKBOX CONFIRMAR TODOS MARCADA --> CONFIRMAR OS OUTROS BILHETES
        if ($confirmarTodos && $bilhete->compra_id) {
            $confirmados = Bilhete::updateAll(
                ['estado' => $bilhete::ESTADO_CONFIRMADO],
                ['compra_id' => $bilhete->compra_id, 'estado' => $bilhete::ESTADO_PENDENTE]
            );
        }

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
