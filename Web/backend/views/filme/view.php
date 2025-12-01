<?php

use backend\components\ActionColumnButtonHelper;
use common\helpers\Formatter;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */
/** @var bool $gerirSessoes */
/** @var bool $gerirFilmes */

$this->title = $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Filmes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex mb-3 gap-1">

                        <?php if ($gerirSessoes && $model->isEstadoEmExibicao()): ?>
                            <?= Html::a('Criar Sessão', ['sessao/create', 'filme_id' => $model->id], [
                                'class' => 'btn btn-success',
                                'title' => 'Criar Sessão',
                                'data-method' => 'post',
                            ]); ?>
                        <?php endif; ?>

                        <?php if ($gerirFilmes): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                            <?php if ($model->isDeletable()): ?>
                                <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'title' => 'Eliminar',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer eliminar este filme permanentemente? Esta ação não pode ser desfeita!',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'titulo',
                            'sinopse:ntext',
                            [
                                'attribute' => 'duracao',
                                'value' => Formatter::minutos($model->duracao),
                            ],
                            'nomesGeneros',
                            'rating',
                            [
                                'attribute' => 'estreia',
                                'value' => Formatter::data($model->estreia),
                            ],
                            'idioma',
                            'realizacao',
                            'trailer_url:url',
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => ActionColumnButtonHelper::filmeEstadoDropdown($model),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'poster_path',
                                'format' => 'raw',
                                'value' => fn($model) => Html::img($model->posterUrl, ['alt' => $model->titulo,
                                    'style' => 'max-width:400px;', 'class' => 'rounded-3 shadow-sm']),
                            ],
                        ],
                    ]) ?>

                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>