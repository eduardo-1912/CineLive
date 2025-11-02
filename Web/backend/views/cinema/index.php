<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use common\models\Cinema;
use common\models\UserProfile;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CinemaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

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


                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'nome',
                            [
                                'label' => 'Morada',
                                'attribute' => 'morada',
                                'value' => function ($model) {
                                    return "{$model->rua}, {$model->codigo_postal} {$model->cidade}";
                                },
                                'headerOptions' => ['style' => 'width: 25rem;'],
                            ],
                            'email:email',
                            'telefone',
                            [
                                'attribute' => 'gerente_id',
                                'value' => 'gerente.profile.nome',
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => function ($model) {
                                    switch ($model->estado) {
                                        case $model::ESTADO_ATIVO: return '<span>Ativo</span>';
                                        case $model::ESTADO_ENCERRADO: return '<span class="text-danger">Encerrado</span>';
                                        default: return '<span class="text-secondary">Desconhecido</span>';
                                    }
                                },
                                'format' => 'raw',
                                'filter' => [
                                    Cinema::ESTADO_ATIVO => 'Ativo',
                                    Cinema::ESTADO_ENCERRADO => 'Encerrado',
                                ],
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {update} {activate} {deactivate}',
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
