<?php

namespace backend\controllers;

use common\models\Filme;
use backend\models\FilmeSearch;
use common\models\Genero;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FilmeController implements the CRUD actions for Filme model.
 */
class FilmeController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['funcionario'],
                        'actions' => ['index', 'view']
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $currentUser = Yii::$app->user;
        $gerirFilmes = $currentUser->can('gerirFilmes');

        $searchModel = new FilmeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $ratingOptions = array_diff_key(Filme::optsRating(), [Filme::RATING_TODOS => null]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gerirFilmes' => $gerirFilmes,
            'ratingOptions' => $ratingOptions,
            'estadoOptions' => Filme::optsEstado(),
        ]);
    }

    public function actionView($id)
    {
        $currentUser = Yii::$app->user;
        $userCinema = $currentUser->identity->profile->cinema;
        $model = $this->findModel($id);

        $gerirSessoes = $currentUser->can('gerirSessoes');
        $gerirSessoesCinema = $currentUser->can('gerirSessoesCinema', ['model' => $userCinema]);
        $gerirFilmes = $currentUser->can('gerirFilmes');

        return $this->render('view', [
            'model' => $model,
            'gerirSessoes' => $gerirSessoes || $gerirSessoesCinema,
            'gerirFilmes' => $gerirFilmes,
        ]);
    }

    public function actionCreate()
    {
        if (!Yii::$app->user->can('gerirFilmes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para criar filmes.');
            return $this->redirect(['index']);
        }

        $model = new Filme();
        $model->estreia = date('Y-m-d');
        $generoOptions = ArrayHelper::map(Genero::find()->orderBy('nome')->all(), 'id', 'nome');

        if ($model->load(Yii::$app->request->post())) {
            $model->posterFile = UploadedFile::getInstance($model, 'posterFile');

            if ($model->validate()) {
                $this->guardarPoster($model);

                if ($model->save()) {
                    $generos = Yii::$app->request->post('Filme')['generosSelecionados'] ?? [];
                    $model->guardarGeneros($generos);

                    Yii::$app->session->setFlash('success', 'Filme criado com sucesso.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

            Yii::$app->session->setFlash('error', 'Erro ao criar o filme.');
        }

        return $this->render('create', [
            'model' => $model,
            'generoOptions' => $generoOptions,
        ]);
    }

    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can('gerirFilmes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para editar filmes.');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        $oldPoster = $model->poster_path;

        $generoOptions = ArrayHelper::map(Genero::find()->orderBy('nome')->all(), 'id', 'nome');

        if ($model->load(Yii::$app->request->post())) {
            $model->posterFile = UploadedFile::getInstance($model, 'posterFile');

            if ($model->validate()) {
                $this->guardarPoster($model, $oldPoster);

                if ($model->save()) {
                    $generos = Yii::$app->request->post('Filme')['generosSelecionados'] ?? [];
                    $model->guardarGeneros($generos);


                    Yii::$app->session->setFlash('success', 'Filme atualizado com successo.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

            Yii::$app->session->setFlash('error', 'Erro ao atualizar o filme.');

        }

        return $this->render('update', [
            'model' => $model,
            'generoOptions' => $generoOptions,
        ]);
    }

    public function actionDelete($id)
    {
        if (!Yii::$app->user->can('gerirFilmes')) {
            Yii::$app->session->setFlash('error', 'Não tem permissão para eliminar filmes.');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);

        if ($model->sessoes) {
            Yii::$app->session->setFlash('error', 'Não pode eliminar filmes com sessões associadas.');
            return $this->redirect(['index']);
        }

        $basePath = Yii::getAlias(Yii::$app->params['posterPath']);
        if ($model->poster_path && is_file($basePath . '/' . $model->poster_path)) {
            @unlink($basePath . '/' . $model->poster_path);
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionChangeStatus($id, $estado)
    {
        if (!Yii::$app->user->can('gerirFilmes')) {
            Yii::$app->session->setFlash('error','Não tem permissão para alterar o estado dos filmes.');
            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);

        $estadoAntigo = $model->estado;
        $model->estado = $estado;
        $model->save();

        if ($estadoAntigo === $model::ESTADO_EM_EXIBICAO && $estadoAntigo !== $model->estado && count($model->getSessoesAtivas()) > 0) {
            Yii::$app->session->setFlash('warning', "O estado foi alterado, as sessões agendadas continuarão visíveis.");
        }
        else {
            Yii::$app->session->setFlash('success', "Estado do filme alterado com sucesso");
        }

        return $this->redirect(['index']);
    }

    private function guardarPoster(Filme $model, ?string $oldPoster = null): void
    {
        if (!$model->posterFile) {
            if ($oldPoster) {
                $model->poster_path = $oldPoster;
            }
            return;
        }

        $basePath = Yii::getAlias(Yii::$app->params['posterPath']);

        // Criar caminho se não existe
        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        // Gerar nome único
        $filename = uniqid('poster_') . '.' . $model->posterFile->extension;

        $savePath = $basePath . DIRECTORY_SEPARATOR . $filename;

        if ($model->posterFile->saveAs($savePath)) {
            $model->poster_path = $filename;

            // Apagar poster antigo se exister
            if ($oldPoster && is_file($basePath . DIRECTORY_SEPARATOR . $oldPoster)) {
                @unlink($basePath . DIRECTORY_SEPARATOR . $oldPoster);
            }
        }
    }
    protected function findModel($id)
    {
        if (($model = Filme::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
