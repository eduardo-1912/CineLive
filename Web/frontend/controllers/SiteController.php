<?php

namespace frontend\controllers;

use common\helpers\Formatter;
use common\models\Cinema;
use common\models\Filme;
use common\models\LoginForm;
use frontend\models\ContactForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

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
    public function actionIndex($cinema_id = null, $filme_id = null, $data = null, $sessao_id = null)
    {
        $filmesCarousel = Filme::findComSessoesAtivas(3);

        $cinemaOptions = ArrayHelper::map(Cinema::findComSessoesAtivas(), 'id', 'nome');
        $filmeOptions = [];
        $dataOptions = [];
        $horaOptions = [];

        if ($cinema_id && isset($cinemaOptions[$cinema_id])) {
            $cinemaSelecionado = Cinema::findOne($cinema_id);
            $filmeOptions = ArrayHelper::map(
                $cinemaSelecionado->getFilmesComSessoesAtivas(),
                'id', 'titulo'
            );

            if ($filme_id && isset($filmeOptions[$filme_id])) {
                $filmeSelecionado = Filme::findOne($filme_id);

                $sessoesPorData = $filmeSelecionado->getSessoesAtivasPorData($cinema_id);
                $dataOptions = array_combine(array_keys($sessoesPorData), array_keys($sessoesPorData));

                if ($data && isset($sessoesPorData[$data])) {
                    $horaOptions = ArrayHelper::map(
                        $sessoesPorData[$data],
                        'id', fn($sessao) => Formatter::hora($sessao->hora_inicio)
                    );
                }
            }
        }

        $filmesMaisVistos = Filme::find()
            ->joinWith('sessoes.bilhetes')
            ->select(['filme.*', 'total' => 'COUNT(bilhete.id)'])
            ->groupBy('filme.id')
            ->orderBy(['total' => SORT_DESC])
            ->limit(4)
            ->all();

        $brevemente = Filme::find()->where(['estado' => Filme::ESTADO_BREVEMENTE])->limit(8)->all();

        return $this->render('index', [
            'filmesCarousel' => $filmesCarousel,
            'cinema_id' => $cinema_id,
            'cinemaOptions' => $cinemaOptions,
            'filme_id' => $filme_id,
            'filmeOptions' => $filmeOptions,
            'data' => $data,
            'dataOptions' => $dataOptions,
            'sessao_id' => $sessao_id,
            'horaOptions' => $horaOptions,
            'filmesMaisVistos' => $filmesMaisVistos,
            'brevemente' => $brevemente,
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
        $currentUser = Yii::$app->user;
        $model = new ContactForm();

        if (!$currentUser->isGuest) {
            $model->name = $currentUser->identity->profile->nome ?? $currentUser->identity->username;
            $model->email = $currentUser->identity->email;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Obrigado pelo seu contacto! Iremos responder o mais breve possível.');
            }
            else {
                Yii::$app->session->setFlash('error', 'Erro ao enviar a mensagem.');
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
