<?php

namespace backend\controllers;

use Exception;
use Yii;
use common\models\User;
use common\models\UserProfile;
use backend\models\UserSearch;
use common\models\Cinema;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
                    [
                        'allow' => true,
                        'roles' => ['gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['index', 'change-status'],
                    ],
                ],
            ],
        ];
    }

    // ADMIN --> TABELA COM TODOS OS UTILIZADORES
    // GERENTE --> TABELA COM OS SEUS FUNCIONÁRIOS (ATIVOS E INATIVOS)
    public function actionIndex()
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para aceder a esta página.');
        }

        // CRIAR SEARCH MODEL E QUERY NA DB
        $searchModel = new UserSearch();
        $params = Yii::$app->request->queryParams;

        $cinemaFilterOptions = ArrayHelper::map(Cinema::find()->select(['id', 'nome'])->orderBy('nome')->all(), 'id', 'nome');
        $statusFilterOptions = $currentUser->can('gerirUtilizadores') ? User::optsStatus() : array_slice(User::optsStatus(), 0, 2, true);

        // ADMIN --> VÊ TODOS OS UTILIZADORES
        if ($currentUser->can('admin')) {
            $dataProvider = $searchModel->search($params);
        }

        // SE FOR GERENTE --> APENAS VÊ OS FUNCIONÁRIOS DO SEU CINEMA
        else {
            $gerenteProfile = $currentUser->identity->profile;

            if (!$gerenteProfile || !$gerenteProfile->cinema_id) {
                throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
            }

            // APLICAR FILTROS
            $params['UserSearch']['cinema_id'] = $gerenteProfile->cinema_id;
            $params['UserSearch']['role'] = 'funcionario';

            $dataProvider = $searchModel->search($params);

            // EXCLUIR UTILIZADORES ELIMINADOS (SOFT-DELETED)
            $dataProvider->query->andWhere(['IN', 'user.status', [User::STATUS_ACTIVE, User::STATUS_INACTIVE]]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'roleFilterOptions' => array_reverse(User::optsRoles(), true),
            'cinemaFilterOptions' => $cinemaFilterOptions,
            'statusFilterOptions' => $statusFilterOptions,
        ]);
    }


    // ADMIN --> VÊ DETALHES DE TODOS OS UTILIZADORES
    // GERENTE --> APENAS VÊ FUNCIONÁRIOS DO SEU CINEMA
    // FUNCIONÁRIO --> VÊ APENAS O SEU PERFIL
    public function actionView($id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // SE NÃO FOR PASSADO ID --> REDIRECIONA PARA PERIFL DO USER ATUAL
        $id = $id ?? $currentUser->id;

        // OBTER UTILIZADOR A VISUALIZAR
        $model = $this->findModel($id);

        $comprasDataProvider = new ActiveDataProvider([
            'query' => $model->getCompras(),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize']],
            'sort' => [
                'defaultOrder' => ['data' => SORT_DESC],
            ],
        ]);

        // SE FOR ADMIN --> PODE VER TODOS OS UTILIZADORES
        if ($currentUser->can('admin')) {
            return $this->render('view', [
                'model' => $model,
                'comprasDataProvider' => $comprasDataProvider,
            ]);
        }

        // SE FOR GERENTE --> PODE VER O SEU PERFIL E DOS FUNCIONÁRIOS DO SEU CINEMA
        if ($currentUser->can('gerente')) {

            // OBTER PERFIL DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE FOR O SEU PRÓPRIO PERFIL
            if ($id == $currentUser->id) {
                return $this->render('view', ['model' => $model]);
            }

            // SE FOR SEU FUNCIONÁRIO --> TEM ACESSO
            if ($gerenteProfile->cinema_id && $model->profile->cinema_id == $gerenteProfile->cinema_id && !$model->isStatusDeleted()) {
                return $this->render('view', ['model' => $model]);
            }

            // CASO CONTRÁRIO --> É REDIRECIONADO PARA O SEU PERFIL
            return $this->redirect(['view', 'id' => $currentUser->id]);
        }

        // OUTROS --> SÓ PODEM VER O SEU PRÓPRIO PERFIL
        if ($id === null || $currentUser->id != $id) {
            return $this->redirect(['view', 'id' => $currentUser->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'comprasDataProvider' => $comprasDataProvider,
        ]);
    }


    // ADMIN --> CRIA QUALQUER UTILIZADOR
    // GERENTE --> APENAS CRIA FUNCIONÁRIOS PARA O SEU CINEMA
    public function actionCreate()
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // CRIAR USER E USER_PROFILE
        $model = new User();
        $profile = new UserProfile();

        // GERAR LISTA DE CINEMAS
        $cinemasOptions = ArrayHelper::map(Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])
            ->orderBy('nome')->all(), 'id', 'nome');

        // SE NÃO FOR ADMIN NEM GERENTE --> SEM ACESSO
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para criar utilizadores.');
        }

        // É ADMIN OU GERENTE --> GUARDAR
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {

            // INICIAR TRANSACTION (TER A CERTEZA QUE NENHUM USER É CRIADO SEM USER_PROFILE)
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // SE USER ATUAL É ADMIN --> PODE CRIAR QUALQUER UTILIZADOR
                if ($currentUser->can('admin')) {
                    if ($model->save()) {

                        // ATRIBUIR USER_PROFILE AO UTILIZADOR CRIADO
                        $profile->user_id = $model->id;

                        // GUARDAR PERFIL
                        $profile->save(false);

                        // ATRIBUIR ROLE RBAC
                        $auth = Yii::$app->authManager;

                        // DAR ASSIGN DO ROLE AO UTILIZADOR NOVO
                        if ($role = $auth->getRole($model->role)) {
                            $auth->assign($role, $model->id);
                        }

                        // SE UTILIZADOR CRIADO FOR GERENTE --> ATUALIZAR CAMPO GERENTE DO CINEMA ESCOLHIDO
                        $this->atualizarGerenteCinema($model, $profile);

                        // DAR COMMIT NA TRANSACTION
                        $transaction->commit();

                        // SE UTILIZADOR NOVO FOR GERENTE/FUNCIONÁRIO E NÃO TEM CINEMA ASSOCIADO --> MENSAGEM DE AVISO
                        if ($model->isStaff() && !$profile->cinema_id) {
                            Yii::$app->session->setFlash('warning', 'O utilizador deve ser associado a um cinema.');
                        }
                        else {
                            Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso.');
                        }

                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }

                // GERENTE --> APENAS PODE CRIAR FUNCIONÁRIOS PARA O SEU CINEMA
                if ($currentUser->can('gerente')) {

                    // OBTER PERFIL DO GERENTE
                    $gerenteProfile = $currentUser->identity->profile;

                    // FORÇAR CRIAÇÃO COM ROLE FUNCIONÁRIO
                    $model->role = 'funcionario';

                    if ($model->save()) {

                        // ATRIBUIR USER_PROFILE AO FUNCIONÁRIO CRIADO
                        $profile->user_id = $model->id;

                        // ASSOCIAR AO CINEMA DO GERENTE
                        $profile->cinema_id = $gerenteProfile->cinema_id;
                        $profile->save(false);

                        // Atribuir papel "funcionario"
                        $auth = Yii::$app->authManager;
                        $auth->assign($auth->getRole('funcionario'), $model->id);

                        // DAR COMMIT NA TRANSACTION
                        $transaction->commit();

                        // MENSAGEM DE SUCESSO E REDIRECIONAR PARA OS DETALHES DO UTILIZADOR NOVO
                        Yii::$app->session->setFlash('success', 'Funcionário criado com sucesso.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
            catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage());
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao criar o utilizador.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'profile' => $profile,
            'cinemasOptions' => $cinemasOptions,
        ]);
    }


    // ADMIN --> EDITA QUALQUER UTILIZADOR
    // GERENTE/FUNCIONÁRIO --> APENAS SE EDITA A SI MESMO
    public function actionUpdate($id = null)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // SE USER ATUAL NÃO FOR ADMIN --> APENAS PODE EDITAR O SEU PERFIL
        if (!$currentUser->can('admin')) {
            $id = $currentUser->id;
        }
        elseif ($id === null) {
            return $this->redirect(['index']);
        }

        // OBTER UTILIZADOR
        $model = $this->findModel($id ?? $currentUser->id);
        $profile = $model->profile ?? new UserProfile(['user_id' => $model->id]);

        // OBTER CINEMAS ATIVOS
        $queryCinemas = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO]);

        // SE PERTENCE A UM CINEMA ENCERRADO --> INCLUIR ESSE CINEMA
        if ($profile->cinema_id) {
            $queryCinemas->orWhere(['id' => $profile->cinema_id]);
        }

        // GERAR LISTA DE CINEMAS
        $cinemasOptions = ArrayHelper::map($queryCinemas->orderBy('nome')->all(), 'id', 'nome');

        // OBTER O ROLE DO UTILIZADOR
        $roles = Yii::$app->authManager->getRolesByUser($model->id);
        $model->role = !empty($roles) ? array_key_first($roles) : null;

        // GUARDAR ALTERAÇÕES
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save()) {

                $profile->user_id = $model->id;
                $profile->save(false);

                // SE O USER ATUAL É ADMIN E O ROLE DO UTILIZADOR A SER ATUALIZADO MUDOU
                if ($currentUser->can('admin') && $model->role) {

                    // RETIRAR TODOS OS ROLES E PERMISSÕES ANTIGAS
                    $auth = Yii::$app->authManager;
                    $auth->revokeAll($model->id);

                    // DAR ASSIGN DO ROLE NOVO
                    if ($role = $auth->getRole($model->role)) {
                        $auth->assign($role, $model->id);
                    }
                }

                // SE O UTILIZADOR A SER ATUALIZADO É GERENTE --> ATUALIZAR CAMPO GERENTE DO CINEMA
                $this->atualizarGerenteCinema($model, $profile);

                // MENSAGEM DE SUCESSO E REDIRECIONAR PARA OS DETALHES DO USER ATUALIZADO
                Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao atualizar o utilizador.');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'profile' => $profile,
            'cinemasOptions' => $cinemasOptions,
        ]);
    }


    // ADMIN --> ELIMINA QUALQUER UTILIZADOR, MENOS A SI MESMO
    public function actionDelete($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER UTILIZADOR A SER ELIMINADO
        $model = $this->findModel($id);

        // SE ADMIN ESTÁ A ELIMINAR OUTRO UTILIZADOR --> HARD-DELETE
        if ($currentUser->can('gerirUtilizadores')) {

            // SE ADMIN/GERENTE TENTAR SE ELIMINAR --> SEM PERMISSÃO
            if ($currentUser->id == $id) {
                Yii::$app->session->setFlash('error', 'Não se pode eliminar a si próprio.');
                return $this->redirect(['index']);
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                // SE UTILIZADOR FOR GERENTE --> REMOVER O SEU ID DOS CINEMAS QUE GERIA
                Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);

                // RETIRAR ROLES RBAC DO UTILIZADOR
                $auth = Yii::$app->authManager;
                $auth->revokeAll($model->id);

                // APAGAR O USER_PROFILE DO UTILIZADOR
                if ($model->profile) {
                    $model->profile->delete();
                }

                // HARD-DELETE
                $model->delete();

                // DAR COMMIT NA TRANSACTION
                $transaction->commit();

                // MENSAGEM DE SUCESSO E REDIRECIONAR PARA INDEX
                Yii::$app->session->setFlash('success', 'Utilizador eliminado permanentemente.');
                return $this->redirect(['index']);
            }
            catch (Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage());
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar o utilizador.');
            }
        }

        // CASO CONTRÁRIO --> SEM PERMISSÃO
        Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar este utilizador.');
        return $this->redirect(['view', 'id' => $currentUser->id]);
    }


    // ADMIN --> MUDA O ESTADO DE QUALQUER UTILIZADOR
    // GERENTE --> APENAS MUDA O ESTADO DOS SEUS FUNCIONÁRIOS
    public function actionChangeStatus($id, $estado)
    {
        $currentUser = Yii::$app->user;

        // VERIFICAR PERMISSÕES
        if (!$currentUser->can('gerirFuncionarios') && !$currentUser->can('gerirUtilizadores')) {
            throw new ForbiddenHttpException('Não tem permissão para alterar o estado de utilizadores.');
        }

        // OBTER O UTILIZADOR A SER ALTERADO
        $model = $this->findModel($id);

        // VERIFICAR SE O ESTADO É VÁLIDO
        $estadosValidos = array_keys(User::optsStatus());
        if (!in_array($estado, $estadosValidos)) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['index']);
        }

        // SE JÁ ESTIVER NO ESTADO PRETENDIDO
        if ($model->status === $estado) {
            Yii::$app->session->setFlash('info', "O utilizador já se encontra no estado selecionado.");
            return $this->redirect(['index']);
        }

        // IMPEDIR ATIVAR O UTILIZADOR SE O SEU CINEMA ESTIVER ENCERRADO
        if ($estado == $model::STATUS_ACTIVE && $model->cinema->isEstadoEncerrado()) {
            Yii::$app->session->setFlash('error', 'Não pode ativar funcionários com cinema encerrado.');
            return $this->redirect(['index']);
        }

        // REGRAS PARA GERENTE
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {

            // IMPEDIR ALTERAR O ESTADO DA PRÓPRIA CONTA
            if ($currentUser->id == $id) {
                Yii::$app->session->setFlash('error', 'Não pode alterar o estado da sua própria conta.');
                return $this->redirect(['view', 'id' => $id]);
            }

            // OBTER PERFIS
            $gerenteProfile = $currentUser->identity->profile;
            $funcionarioProfile = $model->profile;

            // SE FOR SEU FUNCIONÁRIO --> TEM ACESSO
            if (!$gerenteProfile || !$funcionarioProfile || $funcionarioProfile->cinema_id != $gerenteProfile->cinema_id) {
                Yii::$app->session->setFlash('error', 'Só pode alterar o estado de funcionários do seu cinema.');
                return $this->redirect(['index']);
            }

            // IMPEDIR ATIVAR FUNCIONÁRIOS ELIMINADOS
            if ($estado == User::STATUS_ACTIVE && $model->status == User::STATUS_DELETED) {
                Yii::$app->session->setFlash('error', 'Não pode ativar funcionários eliminados.');
                return $this->redirect(['index']);
            }
        }

        // ATUALIZAR ESTDO
        $model->status = $estado;

        if ($model->save(false, ['status'])) {
            $label = User::optsStatus()[$estado] ?? 'Desconhecido';
            Yii::$app->session->setFlash('success', "Estado alterado para '{$label}' com sucesso.");
        }
        else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao alterar o estado do utilizador.');
        }

        return $this->redirect(['index']);
    }


    // ATUALIZAR O GERENTE DE UM CINEMA
    private function atualizarGerenteCinema($model, $profile)
    {
        // OBTER O NOVO CINEMA ESCOLHIDO
        $novoCinema = Cinema::findOne($profile->cinema_id);
        if (!$novoCinema) return;

        // LIBERTAR O GERENTE DOS OUTROS CINEMAS (PARA EVITAR DUPLICADOS)
        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);

        // SE O NOVO CINEMA JÁ TINHA OUTRO GERENTE --> LIMPAR O PERFIL DELE
        if ($novoCinema->gerente_id && $novoCinema->gerente_id != $model->id) {
            UserProfile::updateAll(['cinema_id' => null], ['user_id' => $novoCinema->gerente_id]);
        }

        // ASSOCIAR O NOVO CINEMA AO GERENTE ATUAL
        $novoCinema->gerente_id = $model->id;
        $novoCinema->save(false);
    }


    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
