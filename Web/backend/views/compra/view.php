<?php

use backend\helpers\ActionColumnButtonHelper;
use backend\helpers\LinkHelper;
use common\helpers\Formatter;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Compra */
/* @var $gerirCinemas bool */
/** @var yii\data\ActiveDataProvider $bilhetesDataProvider */

$this->title = $model->nome;
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
                                'value' => LinkHelper::nullSafe($model->cliente->profile->nome ?? null, 'user/view', $model->cliente_id, 'Conta eliminada'),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'sessao_id',
                                'label' => 'Sessão',
                                'format' => 'raw',
                                'value' => LinkHelper::simple($model->sessao->nome, 'sessao/view', $model->sessao_id),
                            ],
                            [
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => LinkHelper::simple($model->sessao->filme->titulo, 'filme/view', $model->sessao->filme_id),
                            ],
                            [
                                'attribute' => 'data',
                                'value' => Formatter::data($model->data),
                            ],
                            [
                                'attribute' => 'total',
                                'value' => Formatter::preco($model->total),
                            ],
                            [
                                'attribute' => 'pagamento',
                                'value' => $model->displayPagamento()
                            ],
                            [
                                'attribute' => 'nomeCinema',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->sessao->cinema->nome, 'cinema/view', $model->sessao->cinema_id),
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