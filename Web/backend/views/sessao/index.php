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

$this->title = 'Sessões';
$this->params['breadcrumbs'][] = $this->title;

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirSessoes = $currentUser->can('gerirSessoes');

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
                                'value' => function ($model) {
                                    return Html::a(Html::encode($model->filme->titulo),
                                    ['filme/view', 'id' => $model->filme_id],
                                    ['class' => 'text-decoration-none text-primary']);
                                },
                                'filter' => ArrayHelper::map(Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])
                                            ->orderBy('titulo')->all(), 'titulo', 'titulo'),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 18rem;'],

                            ],
                            [
                                'attribute' => 'cinema_id',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(
                                        Html::encode($model->cinema->nome),
                                        ['cinema/view', 'id' => $model->cinema_id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
                                'filter' => ArrayHelper::map(Cinema::find()->asArray()->all(), 'id', 'nome'),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 28rem;'],
                                'visible' => $isAdmin,
                            ],
                            [
                                'attribute' => 'numeroSala',
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(
                                        Html::encode($model->sala->nome),
                                        ['sala/view', 'id' => $model->sala_id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
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
                                'value' => function ($model) {
                                    return $model->numeroLugaresDisponiveis . '/' . $model->sala->lugares;
                                },
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => 'estadoFormatado',
                                'format' => 'raw',
                                'filter' => Sessao::optsEstado(),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'headerOptions' => ['style' => 'width: 10rem;'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {update} {delete}',
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
