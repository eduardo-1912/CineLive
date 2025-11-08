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
                                'attribute' => 'cinema_id',
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
                                'attribute' => 'sessao_id',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(
                                        Html::encode($model->sessao->nome),
                                        ['sessao/view', 'id' => $model->sessao->id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },

                            ],
                            [
                                'attribute' => 'data',
                                'value' => 'dataFormatada',
                                'filterInputOptions' => ['class' => 'form-control', 'type' => 'date',],
                            ],
                            [
                                'attribute' => 'total',
                                'value' => fn($model) => $model->totalFormatado . '€',
                            ],
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'filter' => Compra::optsEstado(),
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
