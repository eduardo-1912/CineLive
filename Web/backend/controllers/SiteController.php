<?php

namespace backend\controllers;

use common\models\LoginForm;
use common\models\UserExtension;
use common\models\UserProfile;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
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
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'error'],
                        'allow' => true,
                        'roles' => ['admin', 'gerente', 'funcionario'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'main-login';

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login())
        {
            // OBTER USER ATUAL
            $user = Yii::$app->user->identity;

            // OBTER ROLE RBAC DO USER
            $roles = Yii::$app->authManager->getRolesByUser($user->id);

            // SE USER ATUAL É CLIENTE --> SEM ACESSO
            if (Yii::$app->user->can('cliente')) {
                Yii::$app->user->logout();
                return Yii::$app->response->redirect('../../../frontend/web');
            }

            // SE USER NÃO É ADMIN --> OBTER CINEMA DELE
            if (!Yii::$app->user->can('admin')) {

                // OBTER USER ATUAL
                $userId = Yii::$app->user->id;
                $user = UserExtension::findOne(['id' => $userId]);

                // OBTER O CINEMA DO USER ATUAL
                $cinemaId = $user->profile->cinema_id;

                // SE NÃO TIVER ASSOCIADO A NENHUM CINEMA --> SEM ACESSO
                if ($cinemaId === null) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'Não está associado a nenhum cinema!');
                    return $this->redirect(['login']);
                }
            }

            // SE TUDO BEM --> IR PARA SITE/INDEX
            return $this->goHome();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
