<?php

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

                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                            <?php if (!$model->getSessaos()->exists()): ?>
                                <?= Html::a('<i class="fas fa-skull mr-1"></i> Eliminar', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'title' => 'Eliminar',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer eliminar este filme permanentemente? Esta ação não pode ser desfeita!',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>

                            <div class="btn-group">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= ucfirst($model->estado) ?>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php foreach (Filme::optsEstado() as $estado => $label): ?>
                                        <?php if ($estado !== $model->estado):?>
                                            <li>
                                                <?= Html::a($label, ['change-state', 'id' => $model->id, 'estado' => $estado], [
                                                    'class' => 'dropdown-item',
                                                    'data' => [
                                                        'method' => 'post',
                                                        'confirm' => "Tem a certeza que quer alterar o estado para '{$label}'?",
                                                    ],
                                                ]) ?>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>



                        <?php endif; ?>

                    </div>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'titulo',
                            'sinopse:ntext',
                            'duracao',
                            'rating',
                            'estreia',
                            'idioma',
                            'realizacao',
                            'trailer_url:url',
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
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => $model->estadoFormatado,
                                'format' => 'raw',
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