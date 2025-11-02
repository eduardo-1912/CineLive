<?php

namespace backend\controllers;

use Exception;
use Yii;
use common\models\User;
use common\models\UserProfile;
use backend\models\UserSearch;
use common\models\Cinema;
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
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'funcionarios', 'deactivate', 'activate', 'delete'],
                        'roles' => ['gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'actions' => ['view', 'update'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */

    // TABELA COM CRUD DE TODOS OS UTILIZADORES (APENAS PARA ADMIN)
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // CARREGAR A VIEW COM OS UTILIZADORES
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // VIEW COM CRUD DE FUNCIONÁRIOS DO CINEMA DO GERENTE (APENAS PARA GERENTE)
    public function actionFuncionarios()
    {
        // OBTER USER ATUAL
        $user = Yii::$app->user;

        // SE É ADMIN --> VAI PARA USER/INDEX
        if ($user->can('admin')) {
            return $this->redirect(['index']);
        }

        // SE O USER ATUAL NAO FOR GERENTE --> NÃO TEM PERMISSÃO
        if (!$user->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para aceder a esta página.');
        }

        // É GERENTE --> OBTER CINEMA DO GERENTE
        $gerenteProfile = $user->identity->profile;

        // SE O GERENTE NÃO TIVER CINEMA ASSOCIADO
        if (!$gerenteProfile || !$gerenteProfile->cinema_id) {
            throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
        }

        // CRIAR SEARCH MODEL E FILTROS
        $searchModel = new UserSearch();
        $params = Yii::$app->request->queryParams;

        // FORÇAR FILTRO POR CINEMA DO GERENTE E ROLE 'FUNCIONÁRIO'
        $params['UserSearch']['cinema_id'] = $gerenteProfile->cinema_id;
        $params['UserSearch']['role'] = 'funcionario';

        // BUSCAR DADOS
        $dataProvider = $searchModel->search($params);

        // NÃO MOSTRA FUNCIONÁRIOS ELIMINADOS (SOFT-DELETED)
        $dataProvider->query->andWhere(['IN', 'user.status', [User::STATUS_ACTIVE, User::STATUS_INACTIVE]]);

        // CARREGAR A VIEW COM OS FUNCIONÁRIOS
        return $this->render('funcionarios', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // VER DETALHES DE UM UTILIZADOR (ADMIN VÊ TODOS, GERENTE VÊ DO SEU CINEMA, FUNCIONÁRIO VÊ O SEU)
    public function actionView($id = null) // $id = null porque se user atual apenas mete 'user/view' é redirecionado para o seu perfil
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;
        $currentUserId = $currentUser->id;

        // SE NÃO FOR PASSADO ID --> REDIRECIONA PARA PERIFL DO USER ATUAL
        if ($id === null) {
            return $this->redirect(['view', 'id' => $currentUserId]);
        }

        $model = $this->findModel($currentUserId);

        // SE O UTILIZADOR NÃO EXISTE, REDIRECIONAR PARA O PERFIL DO UTILIZADOR ATUAL
        $model = User::findOne($id);
        if ($model === null) {
            return $this->redirect(['view', 'id' => $currentUserId]);
        }

        // SE FOR ADMIN --> PODE VER TODOS OS UTILIZADORES
        if ($currentUser->can('admin')) {
            $model = $this->findModel($id);
            return $this->render('view', ['model' => $model]);
        }

        // SE FOR GERENTE --> PODE VER O SEU PERFIL E DOS FUNCIONÁRIOS DO SEU CINEMA
        if ($currentUser->can('gerente')) {
            $model = $this->findModel($id);

            // SE FOR O SEU PRÓPRIO PERFIL
            if ($model->id == $currentUserId) {
                return $this->render('view', ['model' => $model]);
            }

            // SE NÃO FOR O PERFIL DO GERENTE --> OBTER CINEMA DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE O CINEMA DO FUNCIONÁRIO FOR IGUAL AO CINEMA DO GERENTE --> TEM ACESSO
            if ($gerenteProfile->cinema_id && $model->profile->cinema_id == $gerenteProfile->cinema_id && $model->status != User::STATUS_DELETED) {
                return $this->render('view', ['model' => $model]);
            }

            // CASO CONTRÁRIO --> É REDIRECIONADO PARA O SEU PERFIL
            return $this->redirect(['view', 'id' => $currentUserId]);
        }

        // OUTROS --> SÓ PODE VER O SEU PRÓPRIO PERFIL
        if ($id === null || $currentUserId != $id) {
            return $this->redirect(['view', 'id' => $currentUserId]);
        }

        $model = $this->findModel($currentUserId);
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    // CRIAR UM NOVO UTILIZADOR (GERENTE PODE CRIAR QUALQUER UTILIZADOR, GERENTE PODE CRIAR FUNCIONÁRIOS PARA O SEU CINEMA)
    public function actionCreate()
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // CRIAR USER E USER_PROFILE
        $model = new User();
        $profile = new UserProfile();

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

                        // SE ROLE ESCOLHIDO NO FORM EXISTIR NO RBAC --> DAR ASSIGN DO UTILIZADOR NOVO
                        if ($role = $auth->getRole($model->role)) {
                            $auth->assign($role, $model->id);
                        }

                        // SE UTILIZADOR CRIADO FOR GERENTE --> ATUALIZAR CAMPO 'gerente_id' DO CINEMA ESCOLHIDO
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

                    // FORÇAR CRIAÇÃO DO FUNCIONÁRIO
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
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // ATUALIZAR UM UTILIZADOR QUE JÁ EXISTE (ADMIN PODE EDITAR TODOS OS UTILIZADORES, GERENTE APENAS EDITA O SEU, FUNCIONÁRIO PODE EDITAR O SEU)
    public function actionUpdate($id = null) // $id = null porque se user atual apenas mete 'user/view' é redirecionado para o seu perfil
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // VER SE USER ATUAL É ADMIN
        $isAdmin = $currentUser->can('admin');

        // SE USER ATUAL NÃO FOR ADMIN --> APENAS PODE EDITAR O SEU PERFIL
        if (!$isAdmin) {
            // SE NÃO FOR PASSADO NENHUM ID OU NÃO FOR O ID DO USER ATUAL --> REDIRECIONAR PARA O UPDATE DO SEU PRÓPRIO PERFIL
            if ($id === null || $currentUser->id != $id) {
                return $this->redirect(['update', 'id' => $currentUser->id]);
            }
        }

        // OBTER USER E PERFIL
        $model = $this->findModel($id ?? $currentUser->id);
        $profile = $model->profile ?? new UserProfile(['user_id' => $model->id]);

        // OBTER O ROLE DO UTILIZADOR
        $roles = Yii::$app->authManager->getRolesByUser($model->id);
        if (!empty($roles)) {
            $model->role = array_key_first($roles);
        }

        // GUARDAR ALTERAÇÕES
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $profile->user_id = $model->id;
                $profile->save(false);

                // SE O USER ATUAL É ADMIN E O ROLE O UTILIZADOR A SER ATUALIZADO MUDOU
                if ($isAdmin && $model->role) {
                    $auth = Yii::$app->authManager;

                    // RETIRAR TODOS OS ROLES E PERMISSÕES ANTIGAS
                    $auth->revokeAll($model->id);

                    // DAR ASSIGN DO ROLE NOVO
                    if ($role = $auth->getRole($model->role)) {
                        $auth->assign($role, $model->id);
                    }
                }

                // SE O UTILIZADOR A SER ATUALIZADO É GERENTE --> ATUALIZAR CAMPO 'gerente_id' DO CINEMA (CASO TENHA MUDADO)
                $this->atualizarGerenteCinema($model, $profile);

                // MENSAGEM DE SUCESSO E REDIRECIONAR PARA OS DETALHES DO USER ATUALIZADO
                Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'profile' => $profile,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

    // ELIMINAR UTILIZADOR (ADMIN DÁ HARD-DELETE A TODOS, GERENTE DÁ SOFT-DELETE AOS FUNCIONÁRIOS, ADMIN SÓ PODE DAR SOFT-DELETE A SI MESMO)
    public function actionDelete($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER UTILIZADOR A SER ELIMINADO
        $user = $this->findModel($id);

        // SHORTCUTS PARA VERIFICAR PERMISSÕES
        $isAdmin = $currentUser->can('admin');
        $isGerente = $currentUser->can('gerente');
        $isOwnAccount = ($currentUser->id == $id);

        // SE ADMIN ESTÁ A ELIMINAR OUTRO UTILIZADOR --> HARD-DELETE
        if ($isAdmin && !$isOwnAccount) {

            // SE UTILIZADOR FOR GERENTE --> LIBERTAR CINEMAS QUE GERIA
            Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $user->id]);

            // RETIRAR ROLES RBAC DO UTILIZADOR
            $auth = Yii::$app->authManager;
            $auth->revokeAll($user->id);

            // APAGAR O USER_PROFILE DO UTILIZADOR
            if ($user->profile) {
                $user->profile->delete();
            }

            // HARD-DELETE
            $user->delete();

            // MENSAGEM DE SUCESSO E REDIRECIONAR PARA INDEX
            Yii::$app->session->setFlash('success', 'Utilizador eliminado permanentemente.');
            return $this->redirect(['index']);
        }

        // SE ADMIN/GERENTE TENTAR SE ELIMINAR --> SOFT-DELETE
        if ($isOwnAccount && $isAdmin) {

            // DAR SOFT-DELETE
            $user->status = User::STATUS_DELETED;

            // MENSAGENS DE SUCESSO/ERRO
            if ($user->save(false, ['status'])) {
                Yii::$app->session->setFlash('success', 'A sua conta foi eliminada.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar a sua conta.');
            }

            // FAZER LOGOUT E REDIRECIONAR PARA PÁGINA DE LOGIN
            Yii::$app->user->logout();
            return $this->redirect(['/site/login']);
        }

        // SE GERENTE ELIMINAR FUNCIONÁRIOS DO SEU CINEMA --> SOFT-DELETE
        if ($isGerente && !$isOwnAccount) {

            // OBTER PERFIL DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE NÃO FOR SEU FUNCIONÁRIO --> SEM PERMISSÃO
            if (!$gerenteProfile || !$user->profile || $user->profile->cinema_id != $gerenteProfile->cinema_id) {
                Yii::$app->session->setFlash('warning', 'Só pode eliminar funcionários do seu cinema.');
                return $this->redirect(['funcionarios']);
            }

            // DAR SOFT-DELETE
            $user->status = User::STATUS_DELETED;

            // MENSAGENS DE SUCESSO/ERRO
            if ($user->save(false, ['status'])) {
                Yii::$app->session->setFlash('success', 'Utilizador eliminado com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar o utilizador.');
            }

            return $this->redirect(['funcionarios']);
        }

        // CASO CONTRÁRIO --> SEM PERMISSÃO
        Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar este utilizador.');
        return $this->redirect(['view', 'id' => $currentUser->id]);
    }

    // DESATIVAR A CONTA DE UM UTILIZADOR (ADMIN PODE TODOS, GERENTE PODE DESATIVAR OS SEUS FUNCIONÁRIOS)
    public function actionDeactivate($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // SE NÃO FOR ADMIN NEM GERENTE --> SEM PERMISSÃO
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para desativar utilizadores.');
            return $this->redirect(['index']);
        }

        // SE FOR GERENTE E TENTAR DESATIVAR O SEU PRÓPRIO PERFIL --> NÃO TEM PERMISSÃO
        if ($currentUser->can('gerente') && !$currentUser->can('admin')  && Yii::$app->user->id == $id) {
            Yii::$app->session->setFlash('warning', 'Não pode desativar a sua própria conta.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // OBTER UTIIZADOR A SER DESATIVADO
        $model = $this->findModel($id);

        // SE FOR GERENTE MAS NÃO FOR ADMIN
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {

            // OBTER PERFIL DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE O CINEMA DO GERENTE NÃO FOR IGUAL AO DO FUNCIONÁRIO PARA DESATIVAR --> SEM PERMISSÃO
            if (!$gerenteProfile || !$model->profile || $model->profile->cinema_id != $gerenteProfile->cinema_id) {
                Yii::$app->session->setFlash('warning', 'Só pode desativar funcionários do seu cinema.');
                return $this->redirect(['funcionarios']);
            }
        }

        // ATUALIZAR STATUS DA CONTA PARA 'INATIVA'
        $model->status = User::STATUS_INACTIVE;

        // GUARDAR
        if ($model->save(false, ['status'])) {
            Yii::$app->session->setFlash('success', 'Utilizador desativado com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao desativar o utilizador.');
        }

        // VOLTAR
        return $currentUser->can('admin') ? $this->redirect(['index']) : $this->redirect(['funcionarios']);
    }

    // ATIVAR UTILIZADOR (ADMIN PODE TODOS, GERENTE PODE ATIVAR OS SEUS FUNCIONÁRIOS)
    public function actionActivate($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // SE NÃO FOR ADMIN NEM GERENTE --> SEM PERMISSÃO
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ativar utilizadores.');
            return $this->redirect(['index']);
        }

        // OBTER UTIIZADOR A SER ATIVADO
        $model = $this->findModel($id);

        // SE FOR GERENTE MAS NÃO FOR ADMIN
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {

            // OBTER PERFIL DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE O CINEMA DO GERENTE NÃO FOR IGUAL AO DO FUNCIONÁRIO PARA ATIVAR --> SEM PERMISSÃO
            if (!$gerenteProfile || !$model->profile || $model->profile->cinema_id != $gerenteProfile->cinema_id) {
                Yii::$app->session->setFlash('warning', 'Só pode ativar funcionários do seu cinema.');
                return $this->redirect(['funcionarios']);
            }

            if ($model->status == User::STATUS_DELETED) {
                Yii::$app->session->setFlash('warning', 'Não pode ativar funcionários eliminados.');
                return $this->redirect(['funcionarios']);
            }

        }

        // ATUALIZAR STATUS DA CONTA PARA 'ATIVA'
        $model->status = User::STATUS_ACTIVE;

        // GUARDAR
        if ($model->save(false, ['status'])) {
            Yii::$app->session->setFlash('success', 'Utilizador ativado com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao ativar o utilizador.');
        }

        // VOLTAR
        return $currentUser->can('admin') ? $this->redirect(['index']) : $this->redirect(['funcionarios']);
    }

    // ATUALIZAR O CAMPO 'gerente_id' DE UM CINEMA
    private function atualizarGerenteCinema($model, $profile)
    {
        // SE UTILIZADOR DEIXOU DE SER GERENTE --> REMOVER DE QUALQUER CINEMA
        if ($model->role !== 'gerente') {
            Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);
            return;
        }

        // SE O GERENTE FICOU SEM CINEMA ASSOCIADO --> REMOVER DO CINEMA
        if (!$profile->cinema_id) {
            Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);
            return;
        }

        // 1 - OBTER O NOVO CINEMA ESCOLHIDO
        $novoCinema = Cinema::findOne($profile->cinema_id);
        if (!$novoCinema) return;

        // 2 - LIBERTAR O GERENTE DOS OUTROS CINEMAS (para evitar duplicados)
        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);

        // SE O NOVO CINEMA JÁ TINHA OUTRO GERENTE --> LIMPAR PERFIL DELE
        if ($novoCinema->gerente_id && $novoCinema->gerente_id != $model->id) {
            UserProfile::updateAll(['cinema_id' => null], ['user_id' => $novoCinema->gerente_id]);
        }

        // 3 - ASSOCIAR O NOVO CINEMA AO GERENTE ATUAL
        $novoCinema->gerente_id = $model->id;
        $novoCinema->save(false);

        // 4 - GARANTIR QUE NENHUM OUTRO GERENTE FICA COM O MESMO CINEMA NO PERFIL
        $gerentes = Yii::$app->authManager->getUserIdsByRole('gerente');
        if (!empty($gerentes)) {
            UserProfile::updateAll(
                ['cinema_id' => null],
                ['and', ['cinema_id' => $novoCinema->id], ['in', 'user_id', $gerentes], ['!=', 'user_id', $model->id],]
            );
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
