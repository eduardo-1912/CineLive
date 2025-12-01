<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use backend\components\LinkHelper;
use common\helpers\Formatter;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $cinema common\models\Cinema|null */
/* @var $gerirSalas bool */
/* @var $searchModel backend\models\SalaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $cinemaOptions array */
/* @var $estadoOptions array */

$this->title = $cinema ? "Salas de {$cinema->nome}" : 'Salas';
$this->params['breadcrumbs'][] = ['label' => $cinema ? $cinema->nome : 'Cinemas',
    'url' => $cinema ? ['cinema/view', 'id' => $cinema->id] : ['index']];
$this->params['breadcrumbs'][] = 'Salas';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?php if ($gerirSalas): ?>
                                <?= Html::a('Criar Sala', ['create'], ['class' => 'btn btn-success']) ?>
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
                                'label' => 'Nome',
                                'attribute' => 'numero',
                                'value' => 'nome'
                            ],
                            'num_filas',
                            'num_colunas',
                            'numeroLugares',
                            [
                                'attribute' => 'preco_bilhete',
                                'value' => fn($model) => Formatter::preco($model->preco_bilhete)
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->cinema->nome, 'cinema/view', $model->cinema_id),
                                'filter' => $cinemaOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 12rem;'],
                                'visible' => !$cinema,
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => 'estadoHtml',
                                'format' => 'raw',
                                'filter' => $estadoOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 9rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $gerirSalas ? '{view} {update} {activate} {close}' : '{view}',
                                'buttons' => ActionColumnButtonHelper::salaButtons(),
                                'headerOptions' => ['style' => 'width: 3rem;'],
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
