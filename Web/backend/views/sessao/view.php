<?php

use backend\components\LinkHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
\yii\web\YiiAsset::register($this);

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if($gerirSessoes): ?>
                            <?php if($model->isEditable()): ?>
                                <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                            <?php endif; ?>
                            <?php if($model->isDeletable()): ?>
                                <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que deseja eliminar esta sessão?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'filme.titulo',
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::filme($model),
                            ],
                            [
                                'label' => 'Cinema',
                                'attribute' => 'cinema.nome',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::cinema($model),
                                'visible' => $gerirCinemas,
                            ],
                            [
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::sala($model),

                            ],
                            [
                                'label' => 'Data',
                                'attribute' => 'dataFormatada',
                            ],
                            'hora',
                            [
                                'label' => 'Lugares Disponíveis',
                                'attribute' => 'lugaresDisponiveis',
                                'value' => fn($model) => $model->numeroLugaresDisponiveis . '/' . $model->sala->lugares,
                            ],
                            [
                                'label' => 'Estado',
                                'attribute' => 'estadoFormatado',
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

    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-md-12">
                    <?= $this->render('_mapaLugares', ['model' => $model]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($model->compras): ?>
        <h3 class="mt-4 mb-3">Compras</h3>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= $this->render('_compras', [
                            'dataProvider' => $comprasDataProvider,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>