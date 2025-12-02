<?php

use backend\components\AppGridView;
use backend\helpers\ActionColumnButtonHelper;
use common\helpers\Formatter;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $gerirFilmes bool */
/* @var $ratingOptions array */
/* @var $estadoOptions array */

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
                            <?php if ($gerirFilmes): ?>
                                <?= Html::a('Criar Filme', ['create'], ['class' => 'btn btn-success']) ?>
                                <?= Html::a('Géneros', ['genero/index'], ['class' => 'btn btn-primary']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

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
                                'value' => fn($model) => Html::img($model->posterUrl, [
                                    'style' => 'width: 4rem; height: 31px; object-fit: cover;',
                                    'class' => 'rounded-1',
                                ]),
                                'headerOptions' => ['style' => 'width: 4rem;'],
                            ],
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'titulo',
                            [
                                'attribute' => 'duracao',
                                'value' => fn($model) => Formatter::minutos($model->duracao),
                            ],
                            [
                                'attribute' => 'estreia',
                                'value' => fn($model) => Formatter::data($model->estreia),
                                'filterInputOptions' => ['type' => 'date', 'class' => 'form-control',],
                            ],
                            [
                                'attribute' => 'rating',
                                'filter' => $ratingOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => ActionColumnButtonHelper::filmeEstadoDropdown($model),
                                'format' => 'raw',
                                'filter' => $estadoOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                                'headerOptions' => ['style' => 'width: 9rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $gerirFilmes ? '{view} {update} {delete}' : '{view}',
                                'headerOptions' => ['style' => 'width: 1rem;'],
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
