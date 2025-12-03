<?php

namespace backend\controllers;

use common\models\AluguerSala;
use common\models\Cinema;
use common\models\Compra;
use common\models\Filme;
use common\models\LoginForm;
use common\models\Sessao;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

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
        $currentUser = Yii::$app->user;
        $cinema = $currentUser->identity->profile->cinema ?? null;

        $verEstatisticas = $currentUser->can('verEstatisticas');
        $verEstatisticasCinema = $currentUser->can('verEstatisticasCinema', ['model' => $cinema]);

        $now = date('Y-m-d');
        $year = date('Y');

        $labelsCinemas = [];
        $valoresVendas = [];

        if ($verEstatisticas) {
            $filmesEmExibicao = Filme::findComSessoesAtivas();
            $totalFilmes = count($filmesEmExibicao);
            $totalSessoes = Sessao::find()->where(['data' => $now])->count();
            $totalAlugueres = AluguerSala::find()->where(['estado' => AluguerSala::ESTADO_PENDENTE])->count();
            $ultimasCompras = Compra::find()->orderBy(['id' => SORT_DESC])->limit(10)->all();

            foreach (Cinema::findAtivos() as $cinema) {
                $total = 0;
                foreach ($cinema->sessoes as $sessao) {
                    foreach ($sessao->compras as $compra) {
                        if (date('Y', strtotime($compra->data)) != $year) continue;
                        foreach ($compra->bilhetes as $bilhete) {
                            $total += $bilhete->preco;
                        }
                    }
                }

                if ($total > 0) {
                    $labelsCinemas[] = $cinema->nome;
                    $valoresVendas[] = $total;
                }
            }
        }
        elseif ($verEstatisticasCinema && $cinema) {
            $filmesEmExibicao = $cinema->getFilmesComSessoesAtivas();
            $totalFilmes = count($filmesEmExibicao);
            $totalAlugueres = $cinema->getAluguerSalas()->where(['estado' => AluguerSala::ESTADO_PENDENTE])->count();
            $totalSessoes = $cinema->getSessoes()->where(['data' => $now])->count();
            $ultimasCompras = $cinema->getCompras()->limit(10)->all();
        }

        return $this->render('index', [
            'verEstatisticas' => $verEstatisticas,
            'verEstatisticasCinema' => $verEstatisticasCinema,
            'totalFilmes' => $totalFilmes,
            'totalAlugueres' => $totalAlugueres,
            'totalSessoes' => $totalSessoes,
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
            $currentUser = Yii::$app->user->identity;

            // Se for cliente
            if ($currentUser->isCliente()) {
                Yii::$app->user->logout();
                Yii::$app->session->setFlash('error', 'Não pode aceder à página de administração.');
                return $this->redirect(['login']);
            }

            // Se for gerente ou funcionário
            if ($currentUser->isGerente() || $currentUser->isFuncionario()) {
                $cinema = Cinema::findOne($currentUser->profile->cinema ?? null);

                // Se não tiver cinema
                if (!$cinema || $cinema->isEstadoEncerrado()) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('error', 'O seu cinema não é válido.');
                    return $this->redirect(['login']);
                }
            }

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
