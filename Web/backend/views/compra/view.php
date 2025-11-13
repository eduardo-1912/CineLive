<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\LinkHelper;
use common\models\Compra;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Compra */

$this->title = 'Compra #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
\yii\web\YiiAsset::register($this);

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'cliente',
                                'value' => fn($model) => LinkHelper::cliente($model),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'sessao_id',
                                'label' => 'Sessão',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::sessao($model),
                            ],
                            [
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::filme($model->sessao),
                            ],
                            'dataFormatada',
                            'totalEmEuros',
                            'pagamentoFormatado',
                            [
                                'attribute' => 'nomeCinema',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::cinema($model->sessao),
                                'visible' => $gerirCinemas,
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => ActionColumnButtonHelper::compraEstadoDropdown($model),
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

    <?php if ($model->bilhetes): ?>
        <h3 class="mt-4 mb-3">Bilhetes</h3>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            <?php if ($model->isEstadoConfirmada() && !$model->isTodosBilhetesConfirmados() && count($model->bilhetes) > 1): ?>
                                <?= Html::a('Confirmar Todos', ['confirm-all-tickets', 'id' => $model->id], [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer confirmar todos os bilhetes desta compra?',
                                        'method' => 'post',
                                    ],
                                ]); ?>
                            <?php endif; ?>
                        </p>
                        <?= $this->render('_bilhetes', [
                            'dataProvider' => $bilhetesDataProvider,
                            'compra' => $model,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>