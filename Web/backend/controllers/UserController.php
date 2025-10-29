<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserExtension;
use common\models\UserProfile;
use backend\models\UserSearch;
use common\models\Cinema;
use yii\web\Controller;
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

    /**
     * Displays a single User model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserExtension();
        $profile = new UserProfile();

        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {

            if ($model->save()) {

                // Associar perfil ao utilizador
                $profile->user_id = $model->id;
                $profile->save(false);

                // Atribuir papel RBAC
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($model->role);
                if ($role) {
                    $auth->assign($role, $model->id);
                }

                // Se for gerente, associar ao cinema correspondente
                if ($model->role === 'gerente' && $profile->cinema_id) {
                    $cinema = Cinema::findOne($profile->cinema_id);
                    if ($cinema) {
                        // Libertar qualquer cinema que este user já gerisse
                        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);

                        // Obter IDs dos utilizadores que são gerentes
                        $gerentesIds = Yii::$app->authManager->getUserIdsByRole('gerente');

                        // Remover o cinema_id apenas de outros GERENTES com este cinema
                        if (!empty($gerentesIds)) {
                            UserProfile::updateAll(
                                ['cinema_id' => null],
                                [
                                    'and',
                                    ['cinema_id' => $cinema->id],
                                    ['in', 'user_id', $gerentesIds],
                                    ['!=', 'user_id', $model->id],
                                ]
                            );
                        }

                        // Associar o novo gerente ao cinema
                        $cinema->gerente_id = $model->id;
                        $cinema->save(false);
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::debug($model->getErrors(), __METHOD__);
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
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $profile = $model->profile ?? new UserProfile(['user_id' => $model->id]);

        // Preenche o papel atual via RBAC
        $roles = Yii::$app->authManager->getRolesByUser($model->id);
        if (!empty($roles)) {
            $model->role = array_key_first($roles);
        }

        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $profile->user_id = $model->id;
                $profile->save(false);

                // Atualizar papel RBAC (remover antigo e adicionar o novo)
                $auth = Yii::$app->authManager;
                $auth->revokeAll($model->id);
                $newRole = $auth->getRole($model->role);
                if ($newRole) {
                    $auth->assign($newRole, $model->id);
                }

                // Se for gerente, associar ao cinema correspondente
                if ($model->role === 'gerente' && $profile->cinema_id) {
                    $cinema = Cinema::findOne($profile->cinema_id);
                    if ($cinema) {
                        // Libertar qualquer cinema que este user já gerisse
                        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);

                        // Obter IDs dos utilizadores que são gerentes
                        $gerentesIds = Yii::$app->authManager->getUserIdsByRole('gerente');

                        // Remover o cinema_id apenas de outros GERENTES com este cinema
                        if (!empty($gerentesIds)) {
                            UserProfile::updateAll(
                                ['cinema_id' => null],
                                [
                                    'and',
                                    ['cinema_id' => $cinema->id],
                                    ['in', 'user_id', $gerentesIds],
                                    ['!=', 'user_id', $model->id],
                                ]
                            );
                        }

                        // Associar o novo gerente ao cinema
                        $cinema->gerente_id = $model->id;
                        $cinema->save(false);
                    }
                } else {
                    // Deixou de ser gerente → libertar cinemas antigos
                    Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $model->id]);
                }

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
        $user = $this->findModel($id);

        Cinema::updateAll(['gerente_id' => null], ['gerente_id' => $user->id]);

        // Retirar Role do User
        $auth = Yii::$app->authManager;
        $auth->revokeAll($user->id);

        // Apagar o Profile (caso exista)
        if ($user->profile) {
            $user->profile->delete();
        }

        // Apagar o User
        $user->delete();

        return $this->redirect(['index']);
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
