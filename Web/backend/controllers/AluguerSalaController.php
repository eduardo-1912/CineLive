<?php

namespace backend\controllers;

use common\components\EmailHelper;
use Yii;
use common\models\AluguerSala;
use backend\models\AluguerSalaSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AluguerSalaController implements the CRUD actions for AluguerSala model.
 */
class AluguerSalaController extends Controller
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
                        'roles' => ['funcionario'],
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

    // ADMIN --> VÊ ALUGUERES DE TODOS OS CINEMAS
    // GERENTE/FUNCIONÁRIO --> VÊ ALUGUERES DO SEU CINEMA
    public function actionIndex($cinema_id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // CRIAR SEARCH MODEL E RECEBER PARÂMETROS DA QUERY
        $searchModel = new AluguerSalaSearch();
        $params = Yii::$app->request->queryParams;

        // ADMIN --> VÊ TODOS OS ALUGUERES
        if ($currentUser->can('admin')) {

            // SE FOI PASSADO CINEMA_ID VIA PARÂMETRO --> APLICAR FILTRO
            if ($cinema_id !== null) {
                $params['AluguerSalaSearch']['cinema_id'] = $cinema_id;
            }

            $dataProvider = $searchModel->search($params);
        }

        // GERENTE/FUNCIONÁRIO --> APENAS VÊ ALUGUERES DO SEU CINEMA
        else {
            $userProfile = $currentUser->identity->profile ?? null;

            // SE NÃO TIVER CINEMA ASSOCIADO --> SEM ACESSO
            if (!$userProfile || !$userProfile->cinema_id) {
                throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
            }

            // SE TENTAR PASSAR CINEMA_ID PELA URL --> IGNORAR
            if ($cinema_id !== null) {
                return $this->redirect(['index']);
            }

            // APLICAR FILTRO PELO SEU CINEMA
            $params['AluguerSalaSearch']['cinema_id'] = $userProfile->cinema_id;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    // ADMIN --> VÊ DETALHES E ATUALIZA ESTADO/SALA DO ALUGUER
    // GERENTE --> VÊ DETALHES E ATUALIZA ESTADO/SALA DO ALUGUER SE FOR DO SEU CINEMA
    // FUNCIONÁRIO --> APENAS VÊ OS DETALHES DO ALUGUER
    public function actionView($id)
    {
        // OBTER O USER ATUAL
        $currentUser =Yii::$app->user;

        // OBTER O ALUGUER
        $model = $this->findModel($id);

        // SE FOR GERENTE/FUNCIONÁRIIO --> SÓ VÊ COMPRAS DO SEU CINEMA
        if (!$currentUser->can('admin')) {

            // OBTER CINEMA DO USER ATUAL
            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            // OBTER CINEMA DO ALUGUER
            $aluguerCinemaId = $model->cinema_id ?? null;

            // SE USER NÃO TIVER CINEMA OU FOR DIFERENTE DO CINEMA DO ALUGUER --> SEM PERMISSÃO
            if (!$userCinemaId || $userCinemaId != $aluguerCinemaId) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para ver este aluguer.');
                return $this->redirect(['index']);
            }
        }

        // SE ATUALIZAR SALA OU ESTADO
        if ($model->load(Yii::$app->request->post())) {

            if (in_array($model->estado, [$model::ESTADO_A_DECORRER, $model::ESTADO_TERMINADO, $model::ESTADO_CANCELADO,])) {
                Yii::$app->session->setFlash('error', 'Não é possível alterar um aluguer que já decorre, terminou ou foi cancelado.');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            // VERIFICAR SE O ESTADO FOI ALTERADO
            $estadoAnterior = $model->getOldAttribute('estado');
            $estadoNovo = $model->estado;

            if ($model->save(false)) {

                // ENVIAR EMAIL SE ESTADO FOI MUDADO
                if ($estadoAnterior !== $estadoNovo) {
                    $cliente = $model->cliente;
                    $nome = $cliente->profile->nome ?? $cliente->username;
                    $email = $cliente->email;

                    // SE ALUGUER FOI CONFIRMADO --> MANDAR EMAIL
                    if ($estadoNovo === $model::ESTADO_CONFIRMADO) {
                        $assunto = 'Confirmação do aluguer de sala - CineLive';
                        $mensagem =
                            "<p>Olá <strong>{$nome}</strong>,</p>
                            <p>O seu <b>aluguer de sala #{$model->id}</b> foi <span style='color:green;'>confirmado</span> com sucesso!</p>
                            <p><b>Data:</b> {$model->dataFormatada}<br>
                               <b>Hora:</b> {$model->horaInicioFormatada} - {$model->horaFimFormatada}<br>
                               <b>Sala:</b> {$model->sala->nome}</p>
                            <p style='margin-top:0.75rem;'>Obrigado por escolher o CineLive.<br><b>Até breve!</b></p>";
                    }

                    // SE ALUGUER FOI CANCELADO --> MANDAR EMAIL
                    elseif ($estadoNovo === $model::ESTADO_CANCELADO) {
                        $assunto = 'Cancelamento do aluguer de sala - CineLive';
                        $mensagem =
                            "<p>Olá <strong>{$nome}</strong>,</p>
                            <p>O seu <b>aluguer de sala #{$model->id}</b> foi <span style='color:#c00;'>cancelado</span>.</p>
                            <p>Se desejar reagendar, entre em contacto com o cinema.</p>
                            <p style='margin-top:0.75rem;'>Cumprimentos,<br><b>Equipa CineLive</b></p>";
                    }

                    // MANDAR EMAIL
                    if (isset($assunto, $mensagem)) {
                        EmailHelper::enviarEmail($email, $assunto, $mensagem);
                        Yii::$app->session->setFlash('info', "Email enviado ao cliente ({$email}).");
                    }
                    }

                Yii::$app->session->setFlash('success', 'Aluguer atualizado com sucesso.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao guardar as alterações.');
            }

            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }


    // ADMIN --> ALTERA O ESTADO DE QUALQUER ALUGUER
    // GERENTE --> ALTERA O ESTADO DE ALUGUERES NO SEU CINEMA
    public function actionChangeStatus($id, $estado)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER ALUGUER
        $model = $this->findModel($id);

        // VERIFICAR PERMISSÃO
        if (!Yii::$app->user->can('gerirAlugueres')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado dos alugueres.');
            return $this->redirect(['index']);
        }

        // VERIFICAR CINEMA (GERENTE/FUNCIONÁRIO --> SÓ O SEU CINEMA)
        if (!$currentUser->can('admin')) {

            $userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

            if ($userCinemaId === null || $userCinemaId != $model->cinema_id) {
                Yii::$app->session->setFlash('error', 'Não tem permissão para alterar alugueres de outro cinema.');
                return $this->redirect(['index']);
            }
        }

        // VERIFICAR SE O ESTADO É VÁLIDO
        $estadosValidos = [AluguerSala::ESTADO_CONFIRMADO, AluguerSala::ESTADO_CANCELADO];
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // BLOQUEAR ALTERAÇÕES DE ALUGUERES CANCELADOS
        if ($model->isEstadoCancelado()) {
            Yii::$app->session->setFlash('error', 'Não é possível alterar o estado de um aluguer já cancelado.');
            return $this->redirect(['index']);
        }

        // BLOQUEAR CANCELAMENTO DE ALUGUERES CONFIRMADOS QUE JÁ COMEÇARAM/TERMINARAM
        if ($model->estado === AluguerSala::ESTADO_CONFIRMADO &&
            ($model->isEstadoADecorrer() || $model->isEstadoTerminado()) &&
            $estado === AluguerSala::ESTADO_CANCELADO)
        {
            Yii::$app->session->setFlash('error', 'Não é possível cancelar um aluguer que já começou ou terminou.');
            return $this->redirect(['index']);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO --> VOLTAR
        if ($model->estado === $estado) {
            Yii::$app->session->setFlash('info', 'O aluguer já se encontra neste estado.');
            return $this->redirect(['index']);
        }

        // ATUALIZAR ESTADO
        $model->estado = $estado;

        if ($model->save(false, ['estado'])) {

            $cliente = $model->cliente;
            $nome = $cliente->profile->nome ?? $cliente->username;
            $email = $cliente->email;

            // SE ALUGUER FOI CANCELADO --> ENVIAR EMAIL
            if ($estado === AluguerSala::ESTADO_CANCELADO) {
                $assunto = 'Cancelamento do aluguer de sala - CineLive';
                $mensagem =
                    "<p>Olá <strong>{$nome}</strong>,</p>
                    <p>O seu <b>aluguer de sala #{$model->id}</b> foi <span style='color:#c00;'>cancelado</span>.</p>
                    <p>Se desejar reagendar, entre em contacto com o cinema.</p>
                    <p style='margin-top:0.75rem;'>Cumprimentos,<br><b>Equipa CineLive</b></p>";

                Yii::$app->session->setFlash('success', 'Aluguer cancelado com sucesso. Email enviado ao cliente.');

            }

            // SE ALUGUER FOI ACEITADO --> ENVIAR EMAIL
            elseif ($estado === AluguerSala::ESTADO_CONFIRMADO) {
                $assunto = 'Confirmação do aluguer de sala - CineLive';
                $mensagem =
                    "<p>Olá <strong>{$nome}</strong>,</p>
                    <p>O seu <b>aluguer de sala #{$model->id}</b> foi <span style='color:green;'>confirmado</span> com sucesso!</p>
                    <p><b>Data:</b> {$model->dataFormatada}<br>
                       <b>Hora:</b> {$model->horaInicioFormatada} - {$model->horaFimFormatada}<br>
                       <b>Sala:</b> {$model->sala->nome}</p>
                    <p style='margin-top:10px;'>Obrigado por escolher o CineLive.<br><b>Até breve!</b></p>";

                Yii::$app->session->setFlash('success', 'Estado do aluguer atualizado e email enviado ao cliente.');
            }

            // ENVIAR EMAIL
            if (isset($assunto, $mensagem)) {
                EmailHelper::enviarEmail($email, $assunto, $mensagem);
            }
        }
        else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar o estado do aluguer.');
        }

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = AluguerSala::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
