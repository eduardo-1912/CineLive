<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserExtension;
use common\models\UserProfile;
use backend\models\UserSearch;
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
                $profile->user_id = $model->id;
                $profile->save(false);

                // Atribuir o papel RBAC
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($model->role);
                if ($role) {
                    $auth->assign($role, $model->id);
                }

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Debug temporário se algo falhar
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
        $profile = $model->profile ?? new \common\models\UserProfile(['user_id' => $model->id]);

        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $profile->user_id = $model->id;
                $profile->save(false);
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
        $this->findModel($id)->delete();

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
