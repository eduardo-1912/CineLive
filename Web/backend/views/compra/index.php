<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use common\models\Cinema;
use common\models\Compra;
use common\models\Sessao;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CompraSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

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
                                'value' => function ($model) {
                                    return $model->cliente && $model->cliente->profile
                                    ? Html::a($model->cliente->profile->nome,
                                            ['user/view', 'id' => $model->cliente->id],
                                            ['class' => 'text-decoration-none text-primary'])
                                    : '<span class="text-muted">Conta eliminada</span>';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'format' => 'raw',
                                'value' => fn($model) =>
                                     Html::a($model->cinema->nome,
                                        ['cinema/view', 'id' => $model->cinema->id],
                                        ['class' => 'text-decoration-none text-primary']
                                    ),
                                'filter' => $cinemaFilterOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 14rem;'],
                                'visible' => Yii::$app->user->can('admin'),
                            ],
                            [
                                'attribute' => 'sessao_id',
                                'format' => 'raw',
                                'value' => fn($model) =>
                                     Html::a($model->sessao->nome,
                                        ['sessao/view', 'id' => $model->sessao->id],
                                        ['class' => 'text-decoration-none text-primary']
                                    ),
                            ],
                            [
                                'attribute' => 'data',
                                'value' => 'dataFormatada',
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
                                'filter' => $estadoFilterOptions,
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
