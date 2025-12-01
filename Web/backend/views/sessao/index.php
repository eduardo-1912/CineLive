<?php

use backend\components\AppGridView;
use backend\components\LinkHelper;
use common\helpers\Formatter;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SessaoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $cinema common\models\Cinema */
/* @var $gerirSessoes bool */
/* @var $cinemaOptions array */
/* @var $estadoOptions array */

$this->title = $cinema ? "Sessões de {$cinema->nome}" : 'Sessões';
if ($cinema) $this->params['breadcrumbs'][] = ['label' => $cinema->nome, 'url' => ['cinema/view', 'id' => $cinema->id]];

$this->params['breadcrumbs'][] = 'Sessões';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?php if($gerirSessoes): ?>
                                <?= Html::a('Criar Sessão', ['create'], ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

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
                                'attribute' => 'tituloFilme',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->filme->titulo, 'filme/view', $model->filme_id),
                                'headerOptions' => ['style' => 'width: 18rem;'],
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->cinema->nome, 'cinema/view', $model->cinema_id),
                                'filter' => $cinemaOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 28rem;'],
                                'visible' => !$cinema && $gerirSessoes,
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
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'type' => 'date',
                                ],
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
                                'label' => 'Lugares Disponíveis',
                                'attribute' => 'lugaresDisponiveis',
                                'value' => fn($model) =>
                                    $model->numeroLugaresDisponiveis . '/' . $model->sala->numeroLugares,
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => 'estadoHtml',
                                'format' => 'raw',
                                'filter' => $estadoOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 14rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $gerirSessoes ? '{view} {update} {delete}' : '{view}',
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
