<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserProfile;
use backend\models\UserSearch;
use common\models\Cinema;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
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
                        'roles' => ['admin', 'gerente'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['view', 'update'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $currentUser = Yii::$app->user;
        $userCinema = $currentUser->identity->profile->cinema ?? null;

        $gerirUtilizadores = $currentUser->can('gerirUtilizadores');
        $verFuncionariosCinema = $currentUser->can('verFuncionariosCinema', ['model' => $userCinema]);

        if (!$gerirUtilizadores && !$verFuncionariosCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver utilizadores.');
            return $this->goHome();
        }

        $searchModel = new UserSearch();
        $params = Yii::$app->request->queryParams;

        if ($userCinema && $verFuncionariosCinema) {
            $params['UserSearch']['cinema_id'] = $userCinema->id;
            $params['UserSearch']['role'] = 'funcionario';
            $dataProvider = $searchModel->search($params);

            // Excluir utilizadores soft-deleted
            $dataProvider->query->andWhere(['IN', 'user.status', [User::STATUS_ACTIVE, User::STATUS_INACTIVE]]);
        }
        else {
            $dataProvider = $searchModel->search($params);
        }

        $statusOptions = $currentUser->can('gerirUtilizadores') ? User::optsStatus() : array_slice(User::optsStatus(), 0, 2, true);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gerirUtilizadores' => $gerirUtilizadores,
            'verFuncionariosCinema' => $verFuncionariosCinema,
            'cinemaOptions' => Cinema::findAllList(),
            'roleOptions' => array_reverse(User::optsRoles(), true),
            'statusOptions' => $statusOptions,
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirUtilizadores = $currentUser->can('gerirUtilizadores');
        $verFuncionariosCinema = $currentUser->can('verFuncionariosCinema', ['model' => $model->profile->cinema]);

        $isOwnAccount = $currentUser->id === $model->id;

        if (!$gerirUtilizadores && !$verFuncionariosCinema && !$isOwnAccount) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para ver este utilizador.');
            return $this->goHome();
        }

        $comprasDataProvider = new ActiveDataProvider([
            'query' => $model->getCompras(),
            'pagination' => ['pageSize' => Yii::$app->params['pageSize']],
            'sort' => ['defaultOrder' => ['data' => SORT_DESC]],
        ]);

        return $this->render('view', [
            'model' => $model,
            'gerirUtilizadores' => $gerirUtilizadores,
            'verFuncionariosCinema' => $verFuncionariosCinema,
            'isOwnAccount' => $isOwnAccount,
            'comprasDataProvider' => $comprasDataProvider,
        ]);
    }

    public function actionCreate()
    {
        $currentUser = Yii::$app->user;
        $userCinema = $currentUser->identity->profile->cinema ?? null;

        $gerirUtilizadores = $currentUser->can('gerirUtilizadores');
        $criarFuncionariosCinema = $currentUser->can('criarFuncionariosCinema', ['model' => $userCinema]);

        $model = new User(['scenario' => 'create']);
        $profile = new UserProfile();

        if (!$gerirUtilizadores && !$criarFuncionariosCinema) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar utilizadores.');
            return $this->goHome();
        }

        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {

            if ($model->save()) {
                // Atribuir user_profile
                $profile->user_id = $model->id;
                $profile->save();

                $auth = Yii::$app->authManager;
                $model->role = $gerirUtilizadores ? $model->role : 'funcionario';
                if ($role = $auth->getRole($model->role)) {
                    $auth->assign($role, $model->id);
                }

                if ($model->isGerente()) {
                    $this->atualizarGerenteCinema($model, $profile);
                }

                Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao criar utilizador.');
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'profile' => $profile,
            'gerirUtilizadores' => $gerirUtilizadores,
            'criarFuncionariosCinema' => $criarFuncionariosCinema,
            'cinemaOptions' => Cinema::findAtivosList(),
            'userCinemaId' => $userCinema->id ?? null,
        ]);
    }

    public function actionUpdate($id)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id ?? $currentUser->id);

        $gerirUtilizadores = $currentUser->can('gerirUtilizadores');
        $isOwnAccount = $currentUser->id === $model->id;

        if (!$gerirUtilizadores && !$isOwnAccount) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar este utilizador.');
            return $this->goHome();
        }

        // Se não tiver profile --> criar novo
        $profile = $model->profile ?? new UserProfile(['user_id' => $model->id]);

        // Obter o role do $model
        $roles = Yii::$app->authManager->getRolesByUser($model->id);
        $model->role = !empty($roles) ? array_key_first($roles) : null;

        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save()) {

                $profile->user_id = $model->id;
                $profile->save();

                // Se o role mudou
                if ($gerirUtilizadores && $model->role) {

                    // Retirar antigos
                    $auth = Yii::$app->authManager;
                    $auth->revokeAll($model->id);

                    // Dar assign do role novo
                    if ($role = $auth->getRole($model->role)) {
                        $auth->assign($role, $model->id);
                    }
                }

                if ($model->isGerente()) {
                    $this->atualizarGerenteCinema($model, $profile);
                }

                Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar o utilizador.');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'profile' => $profile,
            'cinemaOptions' => Cinema::findAllList(),
            'userCinemaId' => $userCinema->id ?? null,
            'gerirUtilizadores' => $gerirUtilizadores,
            'isOwnAccount' => $isOwnAccount,
        ]);
    }

    public function actionChangeStatus($id, $estado)
    {
        $currentUser = Yii::$app->user;
        $model = $this->findModel($id);

        $gerirUtilizadores = $currentUser->can('gerirUtilizadores');
        $alterarEstadoFuncionario = $currentUser->can('alterarEstadoFuncionario', ['model' => $model->profile->cinema ?? null]);
        $isOwnAccount = $currentUser->id === $model->id;

        if (!$gerirUtilizadores && !$alterarEstadoFuncionario) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para alterar o estado de utilizadores.');
            return $this->goHome();
        }

        if ($alterarEstadoFuncionario) {
            if ($isOwnAccount) {
                Yii::$app->session->setFlash('error', 'Não pode alterar o estado da sua própria conta.');
                return $this->redirect(['index']);
            }
            if ($estado == $model::STATUS_ACTIVE && $model->status == $model::STATUS_DELETED) {
                Yii::$app->session->setFlash('error', 'Não pode ativar funcionários eliminados.');
                return $this->redirect(['index']);
            }
        }

        if ($model->status === $estado) {
            Yii::$app->session->setFlash('info', "O utilizador já se encontra no estado selecionado.");
            return $this->redirect(['index']);
        }

        if ($estado == $model::STATUS_ACTIVE && $model->profile->cinema && $model->profile->cinema->isEstadoEncerrado()) {
            Yii::$app->session->setFlash('error', 'Não pode ativar funcionários com cinema encerrado.');
            return $this->redirect(['index']);
        }

        // Atualizar estado
        $model->status = $estado;

        if ($model->save(['status'])) {
            Yii::$app->session->setFlash('success', "Estado do utilizador alterado com sucesso.");
        }
        else {
            Yii::$app->session->setFlash('error', 'Erro ao alterar o estado do utilizador.');
        }

        return $this->redirect(['index']);
    }

    private function atualizarGerenteCinema($model, $profile)
    {
        // Obter o novo cinema escolhido
        $novoCinema = Cinema::findOne($profile->cinema_id);
        if (!$novoCinema) return;

        // Libertar o gerente do cinema
        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);

        // Se o novo cinema já tinha outro gerente --> tirar o cinema_id dele
        if ($novoCinema->gerente_id && $novoCinema->gerente_id != $model->id) {
            UserProfile::updateAll(['cinema_id' => null], ['user_id' => $novoCinema->gerente_id]);
        }

        // Associar o gerente ao novo cinema
        $novoCinema->gerente_id = $model->id;
        $novoCinema->save();
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
