<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use common\models\Cinema;
use common\models\Compra;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CompraSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Compras';
$this->params['breadcrumbs'][] = $this->title;

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');

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
                                'attribute' => 'cliente',
                                'value' => function ($model) {
                                    return Html::a($model->cliente->profile->nome, ['user/view', 'id' => $model->cliente->id]);
                                },
                                'format' => 'raw',
                                'filter' => Html::activeTextInput($searchModel, 'nomeCliente', ['class' => 'form-control',]),
                            ],
                            [
                                'attribute' => 'data',
                                'value' => 'dataFormatada',
                                'filterInputOptions' => ['class' => 'form-control', 'type' => 'date',],
                            ],
                            [
                                'attribute' => 'total',
                                'value' => fn($model) => $model->totalFormatado . '€',
                                'filterInputOptions' => ['class' => 'form-control', 'type' => 'number',],
                            ],
                            [
                                'attribute' => 'pagamento',
                                'value' => fn($model) => $model->displayPagamento(),
                                'filter' => Compra::optsPagamento(),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => $model->estadoFormatado,
                                'format' => 'raw',
                                'filter' => Compra::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                            ],
                            [
                                'attribute' => 'nomeCinema',
                                'label' => 'Cinema',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->cinema
                                        ? Html::a(Html::encode($model->cinema->nome),
                                            ['cinema/view', 'id' => $model->cinema->id],
                                            ['class' => 'text-decoration-none text-primary'])
                                        : '<span class="text-muted">-</span>';
                                },
                                'filter' => ArrayHelper::map(Cinema::find()->asArray()->all(), 'id', 'nome'),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $isAdmin,
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {update} {sessao}',
                                'buttons' => ActionColumnButtonHelper::compraButtons(),
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
