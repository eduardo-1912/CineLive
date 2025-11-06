<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
\yii\web\YiiAsset::register($this);

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirSessoes = $currentUser->can('gerirSessoes');

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if($gerirSessoes): ?>
                            <?php if($model->isEditable()): ?>
                                <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                            <?php endif; ?>
                            <?php if($model->isDeletable()): ?>
                                <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que deseja eliminar esta sessão?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'tituloFilme',
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(Html::encode($model->filme->titulo),
                                        ['filme/view', 'id' => $model->filme_id],
                                        ['class' => 'text-decoration-none text-primary']);
                                },
                            ],
                            [
                                'label' => 'Cinema',
                                'attribute' => 'cinema.nome',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(
                                        Html::encode($model->cinema->nome),
                                        ['cinema/view', 'id' => $model->cinema_id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
                                'visible' => $isAdmin,
                            ],
                            [
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a(
                                        Html::encode($model->sala->nome),
                                        ['sala/view', 'id' => $model->sala_id],
                                        ['class' => 'text-decoration-none text-primary']
                                    );
                                },
                            ],
                            [
                                'label' => 'Data',
                                'attribute' => 'dataFormatada',
                            ],
                            'hora',
                            [
                                'label' => 'Lugares Disponíveis',
                                'attribute' => 'lugaresDisponiveis',
                                'value' => function ($model) {
                                    return $model->numeroLugaresDisponiveis . '/' . $model->sala->lugares;
                                },
                            ],
                            [
                                'label' => 'Estado',
                                'attribute' => 'estadoFormatado',
                                'format' => 'raw',
                            ],
                        ],
                    ]) ?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->




        </div>
        <!--.card-body-->
    </div>
    <!--.card-->


    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-md-12">
                    <?= $this->render('_mapaLugares', ['model' => $model]) ?>
                </div>
            </div>
        </div>
    </div>

</div>