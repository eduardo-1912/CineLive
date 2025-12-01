<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use backend\components\LinkHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CinemaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $estadoFilterOptions array */

$this->title = 'Cinemas';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a('Criar Cinema', ['create'], ['class' => 'btn btn-success']) ?>
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
                                'attribute' => 'nome',
                                'headerOptions' => ['style' => 'width: 12rem;'],
                            ],
                            [
                                'attribute' => 'morada',
                                'headerOptions' => ['style' => 'width: 28rem;'],
                            ],
                            'email:email',
                            'telefone',
                            [
                                'attribute' => 'nomeGerente',
                                'value' => fn($model) => LinkHelper::simple($model->gerente->profile->nome, 'user/view', $model->gerente_id),
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => 'estadoHtml',
                                'format' => 'raw',
                                'filter' => $estadoFilterOptions,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 8rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {update} {activate} {close}',
                                'buttons' => ActionColumnButtonHelper::cinemaButtons(),
                            ],
                        ],
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ]
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
