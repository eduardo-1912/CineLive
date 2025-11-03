<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use common\models\Cinema;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SessaoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirSessoes = $currentUser->can('gerirSessoes');

$this->title = 'Sessões';
$this->params['breadcrumbs'][] = $this->title;
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


                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            [
                                'label' => 'Filme',
                                'attribute' => 'filme.titulo',
                            ],
                            [
                                'attribute' => 'data',
                                'label' => 'Data',
                                'value' => fn($model) => Yii::$app->formatter->asDate($model->data, 'php:d/m/Y'),
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'type' => 'date',
                                ],
                            ],
                            'hora_inicio',
                            'hora_fim',

                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'value' => 'cinema.nome',
                                'filter' => ArrayHelper::map(Cinema::find()->orderBy('nome')->asArray()->all(), 'id', 'nome'),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $isAdmin,
                            ],
                            [
                                'label' => 'Sala',
                                'attribute' => 'sala.numero',
                                'value' => function ($model) {
                                    return 'Sala ' . $model->sala->numero;
                                },
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {update}',
                                //'buttons' => ActionColumnButtonHelper::sessaoButtons(),
                            ],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
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
