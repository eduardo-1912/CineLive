<?php

namespace frontend\controllers;

use common\models\Cinema;
use common\models\Filme;
use common\models\Sessao;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

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
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex($cinema_id = null, $filme_id = null, $data = null, $hora = null)
    {
        /* ============================================================
         * 1. Obter cinemas ativos
         * ============================================================ */
        $cinemas = Cinema::find()
            ->where(['estado' => Cinema::ESTADO_ATIVO])
            ->orderBy('nome')
            ->all();

        $listaCinemas = \yii\helpers\ArrayHelper::map($cinemas, 'id', 'nome');

        /* ============================================================
         * 2. Se veio cinema_id por GET → guardar cookie
         * ============================================================ */
        if ($cinema_id !== null) {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'cinema_id',
                'value' => $cinema_id,
                'expire' => time() + 3600 * 24 * 180,
            ]));
        }

        /* ============================================================
         * 3. Se não veio cinema_id por GET → usar cookie
         * ============================================================ */
        if ($cinema_id === null) {
            $cinema_id = Yii::$app->request->cookies->getValue('cinema_id', null);
        }

        /* ============================================================
         * 4. Carousel — filmes mais recentes com sessões ativas
         * ============================================================ */
        $filmesEmExibicao = Filme::find()
            ->where(['estado' => Filme::ESTADO_EM_EXIBICAO])
            ->orderBy(['id' => SORT_DESC])
            ->limit(10)
            ->all();

        $carouselFilmes = [];

        foreach ($filmesEmExibicao as $f) {
            if ($f->hasSessoesAtivas()) {
                $carouselFilmes[] = $f;
                if (count($carouselFilmes) >= 3) break;
            }
        }


        /* ============================================================
         * 5. Se NÃO há cinema escolhido → não carregar sessões
         * ============================================================ */
        $listaFilmes = [];
        $listaDatas = [];
        $listaHoras = [];
        $sessaoSelecionada = null;

        if ($cinema_id) {

            /* ---- OBTER FILMES DESSE CINEMA ---- */
            $filmes = Filme::find()
                ->alias('f')
                ->joinWith(['sessaos s'])
                ->where(['s.cinema_id' => $cinema_id])
                ->andWhere(['>=', 's.data', date('Y-m-d')])
                ->distinct()
                ->orderBy(['f.titulo' => SORT_ASC])
                ->all();

            $listaFilmes = \yii\helpers\ArrayHelper::map($filmes, 'id', 'titulo');


            /* ---- OBTER SESSÕES DO FILME ESCOLHIDO ---- */
            $sessoes = [];

            if ($filme_id) {
                $sessoes = Sessao::find()
                    ->where([
                        'cinema_id' => $cinema_id,
                        'filme_id' => $filme_id,
                    ])
                    ->andWhere(['>=', 'data', date('Y-m-d')])
                    ->all();

                $sessoes = array_filter($sessoes, fn($s) => $s->isEstadoAtiva());

                /* AGRUPAR POR DATA */
                $sessoesPorData = [];
                foreach ($sessoes as $s) {
                    $sessoesPorData[$s->dataFormatada][] = $s;
                }

                $listaDatas = array_combine(array_keys($sessoesPorData), array_keys($sessoesPorData));

                if ($data && isset($sessoesPorData[$data])) {
                    $listaHoras = \yii\helpers\ArrayHelper::map(
                        $sessoesPorData[$data],
                        'horaInicioFormatada',
                        'horaInicioFormatada'
                    );
                }

                /* Encontrar sessão final */
                if ($data && $hora && isset($sessoesPorData[$data])) {
                    foreach ($sessoesPorData[$data] as $sessao) {
                        if ($sessao->horaInicioFormatada === $hora) {
                            $sessaoSelecionada = $sessao;
                            break;
                        }
                    }
                }
            }
        }

        $filmesMaisVistos = Filme::find()
            ->alias('f')
            ->where(['f.estado' => Filme::ESTADO_EM_EXIBICAO])
            ->select(['f.*', 'COUNT(b.id) AS total_bilhetes'])
            ->joinWith(['sessaos s' => function($q) {
                $q->joinWith('bilhetes b');
            }])
            ->groupBy('f.id')
            ->orderBy(['total_bilhetes' => SORT_DESC])
            ->limit(4)
            ->all();


        $novasEstreias = Filme::findComSessoesFuturas($cinema_id)
            ->orderBy(['id' => SORT_DESC])->limit(8)->all();


        $currentCinema = Cinema::findOne($cinema_id)->nome ?? null;

        /* ============================================================
         * 6. RENDER VIEW
         * ============================================================ */
        return $this->render('index', [
            'listaCinemas' => $listaCinemas,
            'listaFilmes' => $listaFilmes,
            'listaDatas' => $listaDatas,
            'listaHoras' => $listaHoras,
            'cinemas' => $cinemas,
            'currentCinema' => $currentCinema,
            'cinema_id' => $cinema_id,
            'filme_id' => $filme_id,
            'dataSelecionada' => $data,
            'horaSelecionada' => $hora,
            'sessaoSelecionada' => $sessaoSelecionada,
            'carouselFilmes' => $carouselFilmes,
            'filmesMaisVistos' => $filmesMaisVistos,
            'novasEstreias' => $novasEstreias,
        ]);
    }


    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        // CRIAR MODEL
        $model = new ContactForm();

        // OBTER USER ATUAL
        $currentUser = Yii::$app->user;

        // SE ESTIVER AUTENTICADO --> PREENCHER NOME E MAIL
        if (!$currentUser->isGuest) {
            $model->name = $currentUser->identity->profile->nome ?? $currentUser->identity->username;
            $model->email = $currentUser->identity->email;
        }

        // GUARDAR
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Obrigado pelo seu contacto! Iremos responder o mais breve possível.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao enviar a mensagem.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post())) {
            $user = $model->signup();

            if ($user) {
                Yii::$app->user->login($user);

                Yii::$app->session->setFlash('success', 'Conta criada com sucesso!');
                return $this->goHome();
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }



    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}
