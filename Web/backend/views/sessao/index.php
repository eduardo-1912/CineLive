<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use common\models\Cinema;
use common\models\Filme;
use common\models\Sala;
use common\models\Sessao;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SessaoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$currentUser = Yii::$app->user;
$userCinema = $currentUser->identity->profile->cinema;
$isAdmin = $currentUser->can('admin');
$gerirSessoes = $currentUser->can('gerirSessoes');

$actionColumnButtons = $gerirSessoes ? '{view} {update} {delete}' : '{view}';

// ALGUM CINEMA FOI PASSADO POR PARÂMETRO
if (!empty($cinemaId) && $cinemaSelecionado)
{
    $this->title = 'Sessões de ' . $cinemaSelecionado->nome;
    $this->params['breadcrumbs'][] = [
        'label' => $cinemaSelecionado->nome,
        'url' => ['cinema/view', 'id' => $cinemaSelecionado->id]
    ];
}

// VISTA DE ADMIN/GERENTE
else
{
    $this->title = 'Sessões';
    $this->params['breadcrumbs'][] = [
        'label' => $isAdmin ? 'Cinemas' : $userCinema->nome,
        'url' => [$isAdmin ? 'cinema/index' : ('cinema/view?id=' . $userCinema->id)]
    ];
}
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
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => fn($model) =>
                                    Html::a($model->filme->titulo,
                                        ['filme/view', 'id' => $model->filme_id],
                                        ['class' => 'text-decoration-none text-primary']),
                                'headerOptions' => ['style' => 'width: 18rem;'],
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'format' => 'raw',
                                'value' => fn($model) =>
                                    Html::a($model->cinema->nome,
                                        ['cinema/view', 'id' => $model->cinema_id],
                                        ['class' => 'text-decoration-none text-primary']),
                                'filter' => $cinemaFilterOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 28rem;'],
                                'visible' => $isAdmin && empty($cinemaId),
                            ],
                            [
                                'attribute' => 'numeroSala',
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => fn($model) =>
                                    Html::a($model->sala->nome,
                                        ['sala/view', 'id' => $model->sala_id],
                                        ['class' => 'text-decoration-none text-primary']),
                                'headerOptions' => ['style' => 'width: 8rem;'],
                            ],
                            [
                                'attribute' => 'data',
                                'value' => 'dataFormatada',
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'type' => 'date',
                                ],
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
                                'label' => 'Lugares Disponíveis',
                                'attribute' => 'lugaresDisponiveis',
                                'value' => fn($model) =>
                                    $model->numeroLugaresDisponiveis . '/' . $model->sala->lugares,
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => 'estadoFormatado',
                                'format' => 'raw',
                                'filter' => $estadoFilterOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 14rem;'],
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
