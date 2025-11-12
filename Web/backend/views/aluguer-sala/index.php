<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use common\models\AluguerSala;
use common\models\Cinema;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AluguerSalaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alugueres';
$this->params['breadcrumbs'][] = $this->title;

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');

$actionColumnButtons = $isAdmin ? '{view} {delete}' : '{view}';

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
                                'value' => fn($model) =>
                                    $model->cliente && $model->cliente->profile
                                        ? Html::a($model->cliente->profile->nome,
                                            ['user/view', 'id' => $model->cliente->id],
                                            ['class' => 'text-decoration-none text-primary'])
                                        : '<span class="text-muted">Conta eliminada</span>',
                                'format' => 'raw',
                                'filter' => Html::activeTextInput($searchModel, 'nomeCliente', ['class' => 'form-control',]),
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a($model->cinema->nome,
                                        ['cinema/view', 'id' => $model->cinema->id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
                                'filter' => $cinemaFilterOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $isAdmin,
                            ],
                            [
                                'attribute' => 'numeroSala',
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a($model->sala->nome ?? '-',
                                        ['sala/view', 'id' => $model->sala_id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
                                'headerOptions' => ['style' => 'width: 8rem;'],
                            ],
                            [
                                'attribute' => 'data',
                                'value' => 'dataFormatada',
                                'filterInputOptions' => ['class' => 'form-control', 'type' => 'date',],
                            ],
                            [
                                'attribute' => 'hora_inicio',
                                'value' => 'horaInicioFormatada',
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'type' => 'time',
                                ],
                            ],
                            [
                                'attribute' => 'hora_fim',
                                'value' => 'horaFimFormatada',
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'type' => 'time',
                                ],
                            ],
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'value' => fn($model) => ActionColumnButtonHelper::aluguerEstadoDropdown($model),
                                'format' => 'raw',
                                'filter' => $estadoFilterOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 100px;'],
                            ],

                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $actionColumnButtons,
                                'headerOptions' => ['style' => 'width: 1rem;'],
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
