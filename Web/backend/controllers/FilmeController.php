<?php

namespace backend\controllers;

use common\models\Filme;
use backend\models\FilmeSearch;
use Yii;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
                'class' => \yii\filters\AccessControl::class,
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    // VER FILMES
    public function actionIndex()
    {
        $searchModel = new FilmeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $currentUser = Yii::$app->user;
        $gerirFilmes = $currentUser->can('gerirFilmes');

        $actionColumnButtons = $gerirFilmes ? '{view} {update} {delete}' : '{view}';

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'ratingFilterOptions' => FilmeSearch::getRatingFilterOptions(),
            'estadoFilterOptions' => Filme::optsEstado(),
            'gerirFilmes' => $gerirFilmes,
            'actionColumnButtons' => $actionColumnButtons,
        ]);
    }


    // VER DETALHES DE UM FILME
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $currentUser = Yii::$app->user;
        $gerirFilmes = $currentUser->can('gerirFilmes');

        $generos = array_map(fn($g) => Html::encode($g->nome), $model->generos);
        $generos = !empty($generos) ? implode(', ', $generos) : '-';

        return $this->render('view', [
            'model' => $model,
            'generos' => $generos,
            'gerirFilmes' => $gerirFilmes,
        ]);
    }


    // ADMIN --> CRIA FILME
    public function actionCreate()
    {
        // CRIAR NOVO FILME
        $model = new Filme();

        // METER A DATA DE HOJE POR DEFAULT
        if ($model->isNewRecord) {
            $model->estreia = date('Y-m-d');
        }

        if ($model->load(Yii::$app->request->post())) {

            // OBTER FICHEIRO DO POSTER
            $model->posterFile = UploadedFile::getInstance($model, 'posterFile');

            // VALIDAR DADOS
            if ($model->validate()) {

                // GUARDAR POSTER (SE EXISTIR)
                $this->guardarPoster($model);

                // ASSOCIAR GÉNEROS SELECIONADOS
                $model->generosSelecionados = Yii::$app->request->post('Filme')['generosSelecionados'] ?? [];

                // GUARDAR FILME
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Filme criado com sucesso!');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'generosOptions' => FilmeSearch::getGenerosOptions(),
        ]);
    }


    // ADMIN --> EDITA FILME
    public function actionUpdate($id)
    {
        // OBTER FILME
        $model = $this->findModel($id);

        // GUARDAR POSTER ANTIGO (CASO SEJA ALTERADO)
        $oldPoster = $model->poster_path;

        if ($model->load(Yii::$app->request->post())) {

            // OBTER POSTER NOVO (CASO TENHA SIDO ENVIADO)
            $model->posterFile = UploadedFile::getInstance($model, 'posterFile');

            // VALIDAR DADOS
            if ($model->validate()) {

                // GUARDAR POSTER (SUBSTITUI SE NOVO FOI ENVIADO)
                $this->guardarPoster($model, $oldPoster);

                // ASSOCIAR GÉNEROS SELECIONADOS
                $model->generosSelecionados = Yii::$app->request->post('Filme')['generosSelecionados'] ?? [];

                // GUARDAR ALTERAÇÕES
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Filme atualizado com successo.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'generosOptions' => FilmeSearch::getGenerosOptions(),
        ]);
    }


    // ADMIN --> ELIMINA FILME
    public function actionDelete($id)
    {
        // OBTER FILME
        $model = $this->findModel($id);

        // OBTER POSTER
        $basePath = Yii::getAlias(Yii::$app->params['posterPath']);

        // ELIMINAR POSTER
        if ($model->poster_path && is_file($basePath . DIRECTORY_SEPARATOR . $model->poster_path)) {
            @unlink($basePath . DIRECTORY_SEPARATOR . $model->poster_path);
        }

        // ELIMINAR FILME
        $model->delete();

        return $this->redirect(['index']);
    }

    // ADMIN --> MUDA O ESTADO DO FILME
    public function actionChangeStatus($id, $estado)
    {
        // VERIFICAR PERMISSÕES
        if (!Yii::$app->user->can('gerirFilmes')) {
            throw new ForbiddenHttpException('Não tem permissão para alterar o estado dos filmes.');
        }

        $model = $this->findModel($id);

        // VERIFICAR SE O ESTADO É VÁLIDO
        if (!array_key_exists($estado, Filme::optsEstado())) {
            throw new BadRequestHttpException('Estado inválido.');
        }

        // VERIFICAR SE TEM SESSÕES FUTURAS
        $temSessoesFuturas = $model->getSessaos()->where(['>', 'data', date('Y-m-d H:i:s')])->exists();

        // ATUALIZAR ESTADO
        $model->estado = $estado;
        $model->save(false);

        // OBTER ESTADO NOVO
        $label = ucfirst(Filme::optsEstado()[$estado]);

        if ($estado === Filme::ESTADO_TERMINADO && $temSessoesFuturas) {
            Yii::$app->session->setFlash('warning',
                "O filme foi marcado como {$label}, mas ainda tem sessões agendadas. As sessões continuarão visíveis até ocorrerem."
            );
        }
        else {
            Yii::$app->session->setFlash('success',
                "O filme foi alterado para o estado {$label}."
            );
        }

        return $this->redirect(['index']);
    }


    // GUARDAR POSTER DE UM FILME
    private function guardarPoster(Filme $model, ?string $oldPoster = null): void
    {
        // SE NÃO FOI ENVIADO NENHUM POSTER → MANTER ANTIGO
        if (!$model->posterFile) {
            if ($oldPoster) {
                $model->poster_path = $oldPoster;
            }
            return;
        }

        // DEFINIR DIRETÓRIO BASE (PARAMS.PHP)
        $basePath = Yii::getAlias(Yii::$app->params['posterPath']);

        // CRIAR DIRETÓRIO SE NÃO EXISTE
        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        // GERAR NOME ÚNICO PARA O NOVO POSTER
        $filename = uniqid('poster_') . '.' . $model->posterFile->extension;

        // CAMINHO COMPLETO PARA GUARDAR
        $savePath = $basePath . DIRECTORY_SEPARATOR . $filename;

        // GUARDAR FICHEIRO NO SERVIDOR
        if ($model->posterFile->saveAs($savePath)) {
            $model->poster_path = $filename;

            // APAGAR POSTER ANTIGO (SE EXISTIR)
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
