<?php

use backend\components\AppGridView;
use backend\helpers\ActionColumnButtonHelper;
use backend\helpers\LinkHelper;
use common\helpers\Formatter;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AluguerSalaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $gerirCinemas bool */
/* @var $gerirAlugueres bool */
/* @var $cinemaOptions array */
/* @var $estadoOptions array */

$this->title = 'Alugueres';
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
                                'attribute' => 'cliente',
                                'value' => fn($model) => LinkHelper::nullSafe($model->cliente->profile->nome ?? null, 'user/view', $model->cliente_id, 'Conta eliminada'),
                                'format' => 'raw',
                                'filter' => Html::activeTextInput($searchModel, 'nomeCliente', ['class' => 'form-control',]),
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->cinema->nome, 'cinema/view', $model->cinema_id),
                                'filter' => $cinemaOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $gerirCinemas,
                            ],
                            [
                                'attribute' => 'numeroSala',
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->sala->nome, 'sala/view', $model->sala_id),
                                'headerOptions' => ['style' => 'width: 8rem;'],
                            ],
                            [
                                'attribute' => 'data',
                                'value' => fn($model) => Formatter::data($model->data),
                                'filterInputOptions' => ['class' => 'form-control', 'type' => 'date',],
                            ],
                            [
                                'attribute' => 'hora_inicio',
                                'value' => fn($model) => Formatter::hora($model->hora_inicio),
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'type' => 'time',
                                ],
                            ],
                            [
                                'attribute' => 'hora_fim',
                                'value' => fn($model) => Formatter::hora($model->hora_fim),
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
                                'filter' => $estadoOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 100px;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $gerirAlugueres ? '{view} {delete}' : '{view}',
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
