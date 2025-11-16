<?php

namespace backend\controllers;

use common\models\AluguerSala;
use common\models\Bilhete;
use common\models\Cinema;
use common\models\Compra;
use common\models\Filme;
use common\models\LoginForm;
use common\models\Sessao;
use common\models\User;
use common\models\UserProfile;
use Yii;
use yii\db\Expression;
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
        $user = Yii::$app->user;
        $isAdmin = $user->can('admin');
        $isGerente = $user->can('gerente');
        $isFuncionario = $user->can('funcionario');
        $userCinemaId = $user->identity->profile->cinema_id ?? null;

        $now = date('Y-m-d');

        // SE É ADMIN
        if ($isAdmin) {
            $totalFilmesEmExibicao = Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])->count();
            $totalAlugueres = AluguerSala::find()->where(['estado' => AluguerSala::ESTADO_PENDENTE])->count();
            $ultimasCompras = Compra::find()->with(['sessao.filme', 'sessao.cinema'])->orderBy(['id' => SORT_DESC])->limit(10)->all();
            $filmesEmExibicao = Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])->all();

            $anoAtual = date('Y');

            $vendasPorCinema = Compra::find()
                ->alias('c')
                ->joinWith(['bilhetes b', 'sessao s', 'sessao.cinema ci'])
                ->select([
                    'ci.nome AS cinema',
                    new Expression('SUM(b.preco) AS total')
                ])
                ->where(['YEAR(s.data)' => $anoAtual])
                ->groupBy('ci.id')
                ->orderBy(['total' => SORT_DESC])
                ->asArray()
                ->all();

            $labelsCinemas = array_column($vendasPorCinema, 'cinema');
            $valoresVendas = array_map('floatval', array_column($vendasPorCinema, 'total'));
        }

        // SE É GERENTE
        if ($isGerente && !$isAdmin) {
            $totalAlugueres = AluguerSala::find()->where(['estado' => AluguerSala::ESTADO_PENDENTE, 'cinema_id' => $userCinemaId])->count();
            $valoresVendas = [];
        }

        // SE TEM CINEMA
        if ($userCinemaId !== null) {
            $totalFilmesEmExibicao = Filme::find()->joinWith('sessaos s')->where(['>=', 's.data', $now])->andWhere(['s.cinema_id' => $userCinemaId])->distinct()->count();
            $totalAlugueres = AluguerSala::find()->where(['data' => $now, 'cinema_id' => $userCinemaId,])->count();
            $ultimasCompras = Compra::find()
                ->alias('c')
                ->joinWith(['sessao s'])
                ->where(['s.cinema_id' => $userCinemaId])
                ->with(['sessao.filme', 'sessao.cinema'])
                ->orderBy(['c.id' => SORT_DESC])
                ->limit(10)
                ->all();
            $filmesEmExibicao = Filme::getFilmesEmExibicaoPorCinema($userCinemaId);

            $labelsCinemas = [];
            $valoresVendas = [];
        }

        $totalSessoesHoje = Sessao::find()->where(['data' => $now])->andFilterWhere($isAdmin ? [] : ['cinema_id' => $userCinemaId])->count();

        return $this->render('index', [
            'totalFilmesEmExibicao' => $totalFilmesEmExibicao,
            'totalAlugueres' => $totalAlugueres,
            'totalSessoesHoje' => $totalSessoesHoje,
            'ultimasCompras' => $ultimasCompras,
            'filmesEmExibicao' => $filmesEmExibicao,
            'labelsCinemas' => $labelsCinemas,
            'valoresVendas' => $valoresVendas,
        ]);
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
                $user = User::findOne(['id' => $userId]);

                // OBTER O CINEMA DO USER ATUAL
                $cinemaId = $user->profile->cinema_id;

                // SE NÃO TIVER ASSOCIADO A NENHUM CINEMA --> SEM ACESSO
                if ($cinemaId === null) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'Não está associado a nenhum cinema!');
                    return $this->redirect(['login']);
                }

                // OBTER CINEMA DO USER
                $cinema = Cinema::findOne($cinemaId);

                if ($cinema->estado == $cinema::ESTADO_ENCERRADO) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'O seu cinema foi encerrado!');
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
