<?php

use common\models\Cinema;
use common\models\Sala;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\components\AppGridView;
use backend\components\AppActionColumn;
use backend\components\ActionColumnButtonHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SalaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$currentUser = Yii::$app->user;
$userCinema = $currentUser->identity->profile->cinema;
$isAdmin = $currentUser->can('admin');
$gerirSalas = $currentUser->can('gerirSalas');
$actionColumnButtons = $gerirSalas ? '{view} {update} {activate} {close}' : '{view}';

$cinemaSelecionado = !empty($cinemaId) ? Cinema::findOne($cinemaId) : null;

// ALGUM CINEMA FOI PASSADO POR PARÂMETRO
if (!empty($cinemaId) && $cinemaSelecionado)
{
    $this->title = 'Salas de ' . $cinemaSelecionado->nome;
    $this->params['breadcrumbs'][] = [
        'label' => $cinemaSelecionado->nome,
        'url' => ['cinema/view', 'id' => $cinemaSelecionado->id]
    ];
}

// VISTA DE ADMIN/GERENTE
else
{
    $this->title = 'Salas';
    $this->params['breadcrumbs'][] = [
        'label' => $isAdmin ? 'Cinemas' : $userCinema->nome,
        'url' => [$isAdmin ? 'cinema/index' : ('cinema/view?id=' . $userCinema->id)]
    ];
}
$this->params['breadcrumbs'][] = 'Salas';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?php if($isAdmin || $gerirSalas): ?>
                                <?= Html::a('Criar Sala', ['create'], ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            [
                                'label' => 'Nome',
                                'attribute' => 'numero',
                                'value' => 'nome'
                            ],
                            'num_filas',
                            'num_colunas',
                            'lugares',
                            [
                                'attribute' => 'preco_bilhete',
                                'value' => 'precoEmEuros'
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'value' => 'cinema.nome',
                                'filter' => ArrayHelper::map(Cinema::find()->orderBy('nome')->asArray()->all(), 'id', 'nome'),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 12rem;'],
                                'visible' => $isAdmin && empty($cinemaId),
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => 'estadoFormatado',
                                'format' => 'raw',
                                'filter' => Sala::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 9rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $actionColumnButtons,
                                'buttons' => ActionColumnButtonHelper::salaButtons(),
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                        ],
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
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
