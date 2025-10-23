<?php

namespace backend\controllers;

use common\models\Filme;
use backend\models\FilmeSearch;
use Yii;
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
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Filme models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new FilmeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Filme model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Filme model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Filme();

        if ($model->load(Yii::$app->request->post())) {
            $model->posterFile = UploadedFile::getInstance($model, 'posterFile');

            if ($model->validate()) {
                if ($model->posterFile) {
                    $basePath = Yii::getAlias(Yii::$app->params['posterPath']);
                    if (!is_dir($basePath)) {
                        mkdir($basePath, 0775, true);
                    }

                    $filename = uniqid('poster_') . '.' . $model->posterFile->extension;
                    $savePath = $basePath . DIRECTORY_SEPARATOR . $filename;

                    if ($model->posterFile->saveAs($savePath)) {
                        $model->poster_path = $filename;
                    }
                }

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Filme criado com sucesso!');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Filme model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldPoster = $model->poster_path;

        if ($model->load(Yii::$app->request->post())) {
            $model->posterFile = UploadedFile::getInstance($model, 'posterFile');

            if ($model->validate()) {
                if ($model->posterFile) {
                    $basePath = Yii::getAlias(Yii::$app->params['posterPath']);
                    if (!is_dir($basePath)) {
                        mkdir($basePath, 0775, true);
                    }

                    $filename = uniqid('poster_') . '.' . $model->posterFile->extension;
                    $savePath = $basePath . DIRECTORY_SEPARATOR . $filename;

                    if ($model->posterFile->saveAs($savePath)) {
                        $model->poster_path = $filename;

                        // Apagar o poster antigo
                        if ($oldPoster && is_file($basePath . DIRECTORY_SEPARATOR . $oldPoster)) {
                            @unlink($basePath . DIRECTORY_SEPARATOR . $oldPoster);
                        }
                    }
                } else {
                    $model->poster_path = $oldPoster;
                }

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Filme atualizado!');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing Filme model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $basePath = Yii::getAlias(Yii::$app->params['posterPath']);
        if ($model->poster_path && is_file($basePath . DIRECTORY_SEPARATOR . $model->poster_path)) {
            @unlink($basePath . DIRECTORY_SEPARATOR . $model->poster_path);
        }
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Filme model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Filme the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Filme::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
