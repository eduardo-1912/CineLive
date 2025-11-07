<?php

use backend\components\ActionColumnButtonHelper;
use common\models\Filme;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */

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
                        <?php if (Yii::$app->user->can('admin')): ?>

                            <?php if ($model->estado == $model::ESTADO_EM_EXIBICAO): ?>
                                <?= Html::a('Criar Sessão', ['sessao/create', 'filme_id' => $model->id], [
                                    'class' => 'btn btn-success',
                                    'title' => 'Criar Sessão',
                                    'data-method' => 'post',
                                ]); ?>
                            <?php endif; ?>

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
                                'value' => function ($model) {
                                    return $model->duracao . ' minutos';
                                }
                            ],
                            [
                                'label' => 'Géneros',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $generos = array_map(fn($g) => Html::encode($g->nome), $model->generos);
                                    return !empty($generos)
                                        ? implode(', ', $generos)
                                        : '-';
                                },
                            ],
                            'rating',
                            'estreia',
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
                                'value' => function ($model) {
                                    if ($model->poster_path) {
                                        return Html::img($model->getPosterUrl(), [
                                            'alt' => $model->titulo,
                                            'style' => 'max-width:400px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);'
                                        ]);
                                    }
                                    return Html::tag('span', 'Sem poster', ['class' => 'text-muted']);
                                },
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