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
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionFuncionarios()
    {
        $user = Yii::$app->user;

        // Só gerentes (ou admin) podem aceder
        if (!$user->can('gerente') && !$user->can('admin')) {
            throw new ForbiddenHttpException('Não tem permissão para aceder a esta página.');
        }

        if ($user->can('admin')) {
            return $this->redirect(['index']);
        }

        // Obter cinema do gerente
        $gerenteProfile = $user->identity->profile;
        if (!$gerenteProfile || !$gerenteProfile->cinema_id) {
            throw new ForbiddenHttpException('Não está associado a nenhum cinema.');
        }

        // Criar search model e data provider com filtro automático
        $searchModel = new UserSearch();
        $params = Yii::$app->request->queryParams;

        // Forçar filtro por cinema e role
        $params['UserSearch']['cinema_id'] = $gerenteProfile->cinema_id;
        $params['UserSearch']['role'] = 'funcionario';

        $dataProvider = $searchModel->search($params);

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
    public function actionView($id = null)
    {
        $currentUser = Yii::$app->user;
        $currentUserId = $currentUser->id;

        // Se for Admin --> pode ver tudo
        if ($currentUser->can('admin')) {
            $model = $this->findModel($id ?? $currentUserId);
            return $this->render('view', ['model' => $model]);
        }

        // Se for Gerente --> pode ver o seu perfil ou de funcionários do seu cinema
        if ($currentUser->can('gerente')) {
            $model = $this->findModel($id ?? $currentUserId);

            // Se for o próprio perfil
            if ($model->id == $currentUserId) {
                return $this->render('view', ['model' => $model]);
            }

            // Obter cinema do gerente
            $gerenteProfile = $currentUser->identity->profile;
            if ($gerenteProfile->cinema_id) {
                // Funcionário pertence ao mesmo cinema?
                if ($model->profile->cinema_id == $gerenteProfile->cinema_id) {
                    return $this->render('view', ['model' => $model]);
                }
            }

            // Caso contrário --> sem permissão
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para ver este utilizador.');
        }

        // Outros --> só pode ver o próprio perfil
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
    public function actionCreate()
    {
        $currentUser = Yii::$app->user;
        $model = new UserExtension();
        $profile = new UserProfile();

        // Se não for admin nem gerente --> sem acesso
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para criar utilizadores.');
        }

        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {

            // ADMIN --> pode criar qualquer utilizador
            if ($currentUser->can('admin')) {
                if ($model->save()) {
                    $profile->user_id = $model->id;
                    $profile->save(false);

                    // RBAC
                    $auth = Yii::$app->authManager;
                    if ($role = $auth->getRole($model->role)) {
                        $auth->assign($role, $model->id);
                    }

                    // Atualizar cinema se for gerente
                    self::atualizarGerente($model, $profile);

                    Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

            // GERENTE --> pode criar funcionários para o seu cinema
            if ($currentUser->can('gerente')) {
                $gerenteProfile = $currentUser->identity->profile;

                // Forçar criação de funcionário do seu cinema
                $model->role = 'funcionario';
                if ($model->save()) {
                    $profile->user_id = $model->id;
                    $profile->cinema_id = $gerenteProfile->cinema_id; // associa ao cinema do gerente
                    $profile->save(false);

                    // Atribuir papel "funcionario"
                    $auth = Yii::$app->authManager;
                    $auth->assign($auth->getRole('funcionario'), $model->id);

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
    public function actionUpdate($id = null)
    {
        $currentUser = Yii::$app->user;
        $isAdmin = $currentUser->can('admin');

        // Se não for admin, só pode editar o próprio perfil
        if (!$isAdmin) {
            if ($id === null || $currentUser->id != $id) {
                return $this->redirect(['update', 'id' => $currentUser->id]);
            }
        }

        // Carregar o modelo e perfil
        $model = $this->findModel($id ?? $currentUser->id);
        $profile = $model->profile ?? new UserProfile(['user_id' => $model->id]);

        // Preencher role atual
        $roles = Yii::$app->authManager->getRolesByUser($model->id);
        if (!empty($roles)) {
            $model->role = array_key_first($roles);
        }

        // Guardar alterações
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $profile->user_id = $model->id;
                $profile->save(false);

                if ($isAdmin && $model->role) {
                    $auth = Yii::$app->authManager;
                    $auth->revokeAll($model->id);
                    if ($role = $auth->getRole($model->role)) {
                        $auth->assign($role, $model->id);
                    }
                }

                self::atualizarGerente($model, $profile);
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
    public function actionDelete($id)
    {
        // Se não for admin
        if (!Yii::$app->user->can('admin')) {
            // Se tentar eliminar outro perfil
            if (Yii::$app->user->id != $id) {
                return $this->redirect(['view', 'id' => Yii::$app->user->id]);
            }
        }

        $user = $this->findModel($id);

        // Libertar cinemas geridos por este utilizador (se for gerente)
        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $user->id]);

        // Retirar todos os papéis RBAC
        $auth = Yii::$app->authManager;
        $auth->revokeAll($user->id);

        // Apagar o perfil, se existir
        if ($user->profile) {
            $user->profile->delete();
        }

        // Apagar o utilizador
        $user->delete();

        // Se for admin --> volta ao index
        if (Yii::$app->user->can('admin')) {
            return $this->redirect(['index']);
        }

        // Se o próprio utilizador se apagou, terminar sessão
        Yii::$app->user->logout();
        return $this->redirect(['/site/login']);
    }

    public function actionDeactivate($id)
    {
        $currentUser = Yii::$app->user;

        // Apenas admin ou gerente podem desativar
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para desativar utilizadores.');
        }

        $model = $this->findModel($id);

        // Gerente: só pode desativar funcionários do seu cinema
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {
            $gerenteProfile = $currentUser->identity->profile;

            if (!$gerenteProfile || !$model->profile || $model->profile->cinema_id != $gerenteProfile->cinema_id) {
                throw new \yii\web\ForbiddenHttpException('Só pode desativar funcionários do seu cinema.');
            }
        }

        // Atualizar status
        $model->status = 9; // Inativa
        if ($model->save(false, ['status'])) {
            Yii::$app->session->setFlash('success', 'Utilizador desativado com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao desativar o utilizador.');
        }

        return $currentUser->can('admin') ? $this->redirect(['index']) : $this->redirect(['funcionarios']);
    }


    public function actionActivate($id)
    {
        $currentUser = Yii::$app->user;

        // Apenas admin ou gerente podem ativar
        if (!$currentUser->can('admin') && !$currentUser->can('gerente')) {
            throw new \yii\web\ForbiddenHttpException('Não tem permissão para ativar utilizadores.');
        }

        $model = $this->findModel($id);

        // Gerente: só pode ativar funcionários do seu cinema
        if ($currentUser->can('gerente') && !$currentUser->can('admin')) {
            $gerenteProfile = $currentUser->identity->profile;

            if (!$gerenteProfile || !$model->profile || $model->profile->cinema_id != $gerenteProfile->cinema_id
            ) {
                throw new \yii\web\ForbiddenHttpException('Só pode ativar funcionários do seu cinema.');
            }
        }

        // Atualizar status
        $model->status = 10; // Ativo
        if ($model->save(false, ['status'])) {
            Yii::$app->session->setFlash('success', 'Utilizador ativado com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Ocorreu um erro ao ativar o utilizador.');
        }

        return $currentUser->can('admin') ? $this->redirect(['index']) : $this->redirect(['funcionarios']);
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

    //
    private static function atualizarGerente($model, $profile)
    {
        // Se deixou de ser gerente --> remover de todos os cinemas
        if ($model->role !== 'gerente') {
            Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);
            return;
        }

        // Se não tiver cinema atribuído → nada a fazer
        if (!$profile->cinema_id) {
            return;
        }

        // Se não encontrar nenhum cinema gerido por este gerente --> voltar
        $cinema = Cinema::findOne($profile->cinema_id);
        if (!$cinema) {
            return;
        }

        // Associar este gerente ao cinema
        $cinema->gerente_id = $model->id;
        $cinema->save(false);

        // Libertar o cinema de outros gerentes que já o tinham
        $gerentes = Yii::$app->authManager->getUserIdsByRole('gerente');
        UserProfile::updateAll(
            ['cinema_id' => null],
            [
                'cinema_id' => $cinema->id,
                'user_id' => $gerentes,
                ['!=', 'user_id', $model->id],
            ]
        );
    }

}
