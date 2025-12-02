<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use backend\components\LinkHelper;
use common\helpers\Formatter;
use common\models\Cinema;
use common\models\Compra;
use common\models\Sessao;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CompraSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $cinemaOptions array */


$this->title = 'Compras';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            [
                                'attribute' => 'nomeCliente',
                                'label' => 'Cliente',
                                'value' => fn($model) => LinkHelper::condition($model->cliente->profile->nome ?? null, 'user/view', $model->cliente_id, 'Conta eliminada'),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->sessao->cinema->nome, 'cinema/view', $model->sessao->cinema_id),
                                'filter' => $cinemaOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 14rem;'],
                                'visible' => $gerirCinemas,
                            ],
                            [
                                'attribute' => 'sessao_id',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->sessao->nome, 'sessao/view', $model->sessao_id),
                            ],
                            [
                                'attribute' => 'data',
                                'value' => fn($model) => Formatter::data($model->data),
                                'filterInputOptions' => ['class' => 'form-control', 'type' => 'date',],
                            ],
                            [
                                'attribute' => 'total',
                                'value' => 'totalEmEuros',
                            ],
                            'numeroBilhetes',
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'filter' => $estadoOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'value' => fn($model) => ActionColumnButtonHelper::compraEstadoDropdown($model),
                                'headerOptions' => ['style' => 'width: 9rem'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {cancel} {confirm} {confirmarBilhetes}',
                                'buttons' => ActionColumnButtonHelper::compraButtons(),
                                'headerOptions' => ['style' => 'width: 3rem'],
                            ],
                        ],
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
