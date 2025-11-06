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
                            <?= Html::a('Salas', ['sala/index'], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('Sessões', ['sessao/index'], ['class' => 'btn btn-secondary']) ?>
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
                                'value' => fn($model) => $model->estadoFormatado,
                                'format' => 'raw',
                                'filter' => Cinema::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
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
