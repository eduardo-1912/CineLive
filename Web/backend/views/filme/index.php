<?php

use backend\assets\AppAsset;
use backend\components\ActionColumnButtonHelper;
use common\models\Cinema;
use common\models\Filme;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\AppGridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Filmes';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?php if (Yii::$app->user->can('gerirFilmes')): ?>
                                <?= Html::a('Criar Filme', ['create'], ['class' => 'btn btn-success']) ?>
                                <?= Html::a('Géneros', ['genero/index'], ['class' => 'btn btn-primary']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- <?= $this->render('_search', ['model' => $searchModel]); ?> -->

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'poster_path',
                                'format' => 'raw',
                                'value' => fn($model) => Html::img($model->getPosterUrl(), [
                                    'style' => 'width: 3rem; border-radius:4px;'
                                ]),
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'titulo',
                            [
                                'attribute' => 'duracao',
                                'value' => function ($model) {
                                    return $model->duracao . ' minutos';
                                }
                            ],
                            [
                                'attribute' => 'estreia',
                                'value' => fn($model) => $model->estreiaFormatada,
                                'filterInputOptions' => ['type' => 'date', 'class' => 'form-control',],
                            ],
                            [
                                'attribute' => 'rating',
                                'filter' => array_diff_key(Filme::optsRating(), [Filme::RATING_TODOS => null]),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => $model->estadoFormatado,
                                'format' => 'raw',
                                'filter' => Filme::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                                'headerOptions' => ['style' => 'width: 9rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {createSessao} {update} {delete}',
                                'buttons' => ActionColumnButtonHelper::filmeButtons(),
                            ],
                        ]
                    ]); ?>



                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
