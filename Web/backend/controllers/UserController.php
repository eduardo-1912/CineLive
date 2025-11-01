<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserExtension;
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
                        'actions' => ['create', 'funcionarios', 'deactivate', 'activate'],
                        'roles' => ['gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'actions' => ['view', 'update', 'delete'],
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

        // SE FOR ADMIN --> PODE VER TODOS OS UTILIZADORES
        if ($currentUser->can('admin')) {
            $model = $this->findModel($id ?? $currentUserId); // se id == null é redirecionado para o seu perfil
            return $this->render('view', ['model' => $model]);
        }

        // SE FOR GERENTE --> PODE VER O SEU PERFIL && PERFIL DOS FUNCIONÁRIOS DO SEU CINEMA
        if ($currentUser->can('gerente')) {
            $model = $this->findModel($id ?? $currentUserId);

            // SE FOR O SEU PRÓPRIO PERFIL
            if ($model->id == $currentUserId) {
                return $this->render('view', ['model' => $model]);
            }

            // SE NÃO FOR O PERFIL DO GERENTE --> OBTER CINEMA DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE GERENTE ESTÁ ASSOCIADO A ALGUM CINEMA
            if ($gerenteProfile->cinema_id) {

                // SE O CINEMA DO FUNCIONÁRIO FOR IGUAL AO CINEMA DO GERENTE --> TEM ACESSO
                if ($model->profile->cinema_id == $gerenteProfile->cinema_id) {
                    return $this->render('view', ['model' => $model]);
                }
            }

            // CASO CONTRÁRIO --> SEM PERMISSÃO
            throw new ForbiddenHttpException('Não tem permissão para ver este utilizador.');
        }

        // OUTROS --> SÓ PODE VER O SEU PRÓPRIO PERFIL
        if ($id === null || $currentUserId != $id) {
            return $this->redirect(['view', 'id' => $currentUserId]);
        }

        // OBTER O UTILIZADOR
        $model = $this->findModel($currentUserId);

        // CARREGAR A VIEW COM DETALHES DO UTILIZADOR
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
        $model = new UserExtension();
        $profile = new UserProfile();

        // SE NÃO FOR ADMIN NEM GERENTE --> SEM ACESSO
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new ForbiddenHttpException('Não tem permissão para criar utilizadores.');
        }

        // É ADMIN OU GERENTE --> GUARDAR
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {

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
                    self::atualizarGerente($model, $profile);

                    // MENSAGEM DE SUCESSO E REDIRECT PARA DETALHES DO UTILIZADOR NOVO
                    Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso.');
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

                    // MENSAGEM DE SUCESSO E REDIRECIONAR PARA OS DETALHES DO UTILIZADOR NOVO
                    Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
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
                self::atualizarGerente($model, $profile);

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

    // ELIMINAR UTILIZADOR (ADMIN DÁ HARD-DELETE A TODOS, GERENTE DÁ HARD-DELETE A TODOS OS SEUS FUNCIONÁRIOS, ADMIN/GERENTE SÓ PODEM DAR SOFT-DELETE A SI MESMO)
    public function actionDelete($id)
    {
        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // OBTER UTILIZADOR A SER ELIMINADO
        $user = $this->findModel($id);

        // SHORTCUTS PARA VERIFICAR PERMISSÕES
        $isAdmin = $currentUser->can('admin');
        $isGerente = $currentUser->can('gerente');
        $isFuncionario = $currentUser->can('funcionario');
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
        if ($isOwnAccount && ($isAdmin || $isGerente)) {

            // DAR SOFT-DELETE
            $user->status = User::STATUS_DELETED;

            // MENSAGENS DE SUCESSO/ERRO
            if ($user->save(false, ['status'])) {
                Yii::$app->session->setFlash('success', 'A sua conta foi eliminada.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao eliminar a conta.');
            }

            // FAZER LOGOUT E REDIRECIONAR PARA PÁGINA DE LOGIN
            Yii::$app->user->logout();
            return $this->redirect(['/site/login']);
        }

        // SE GERENTE ELIMINAR FUNCIONÁRIOS DO SEU CINEMA --> HARD-DELETE
        if ($isGerente && !$isOwnAccount) {

            // OBTER PERFIL DO GERENTE
            $gerenteProfile = $currentUser->identity->profile;

            // SE NÃO FOR SEU FUNCIONÁRIO --> SEM PERMISSÃO
            if (!$gerenteProfile || !$user->profile || $user->profile->cinema_id != $gerenteProfile->cinema_id) {
                Yii::$app->session->setFlash('warning', 'Só pode eliminar funcionários do seu cinema.');
                return $this->redirect(['funcionarios']);
            }

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
    private static function atualizarGerente($model, $profile)
    {
        // SE UTILIZADOR A SER EDITADO DEIXOU DE SER GERENTE --> REMOVER DO CINEMA
        if ($model->role !== 'gerente') {
            Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);
            return;
        }

        // SE NÃO TIVER CINEMA ATRIBUÍDO --> VOLTAR
        if (!$profile->cinema_id) {
            return;
        }

        // OBTER CINEMA QUE ESTÁ NO PERFIL
        $cinema = Cinema::findOne($profile->cinema_id);

        // SE NÃO ENCONTRAR NENHUM CINEMA GERIDO POR ESTE GERENTE --> VOLTAR
        if (!$cinema) {
            return;
        }

        // ASSOCIAR ID DO NOVO GERENTE AO CINEMA ESCOLHIDO
        $cinema->gerente_id = $model->id;
        $cinema->save(false);

        // OBTER TODOS OS GERENTES
        $gerentes = Yii::$app->authManager->getUserIdsByRole('gerente');

        // REMOVER O CINEMA DE OUTROS GERENTES QUE TINHAM ESTE CINEMA NO SEU USER_PROFILE
        UserProfile::updateAll( // (ATRIBUTOS, CONDIÇÕES)

            // PARA TODAS AS LINHAS QUE CORRESPONDAM A CONDIÇÃO --> METE 'cinema_id' == NULL
            ['cinema_id' => null],

            // SÓ PERFIS QUE PERTENÇAM A ESTE CINEMA, ONDE 'user_id' ESTEJA NA LISTA DE GERENTES, EXCETO O GERENTE NOVO
            ['cinema_id' => $cinema->id, 'user_id' => $gerentes, ['!=', 'user_id', $model->id]]
        );
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
        if (($model = UserExtension::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
