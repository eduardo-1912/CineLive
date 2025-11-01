<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Filmes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </p>

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
                                            'style' => 'max-width:200px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);'
                                        ]);
                                    }
                                    return Html::tag('span', 'Sem poster', ['class' => 'text-muted']);
                                },
                            ],
                            'estado',
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