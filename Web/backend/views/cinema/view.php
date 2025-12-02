<?php

use backend\helpers\LinkHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */
/* @var $currentUser yii\web\User */
/* @var $gerirCinemas bool */
/* @var $verCinema bool */
/* @var $editarCinema bool */
/* @var $gerirSalas bool */
/* @var yii\data\ActiveDataProvider $salasDataProvider */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = $gerirCinemas ? ['label' => 'Cinemas', 'url' => ['index']] : ['label' => 'Cinemas'];
$this->params['breadcrumbs'][] = $model->nome;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?= Html::a('Sessões', ['sessao/index', 'cinema_id' => $model->id], ['class' => 'btn btn-secondary']) ?>

                        <?php if ($gerirCinemas || $editarCinema): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                            <?php if ($gerirCinemas): ?>
                                <?php if ($model->isClosable()): ?>
                                    <?= Html::a('Encerrar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ENCERRADO], [
                                        'class' => 'btn btn-danger',
                                        'data' => ['confirm' => 'Tem a certeza que quer encerrar este cinema?', 'method' => 'post',],
                                    ]) ?>
                                <?php elseif ($model->isActivatable()): ?>
                                    <?= Html::a('Ativar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ATIVO], [
                                        'class' => 'btn btn-success',
                                        'data' => ['confirm' => 'Tem a certeza que quer ativar este cinema?', 'method' => 'post',],
                                    ]) ?>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                    </p>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nome',
                            [
                                'attribute' => 'nomeGerente',
                                'format' => 'raw',
                                'value' => $gerirCinemas ? LinkHelper::simple($model->gerente->profile->nome,
                                    'user/view', $model->gerente_id) : $model->gerente->profile->nome,
                                'visible' => !$editarCinema,
                            ],
                            'email:email',
                            'telefone',
                            'morada',
                            'horario',
                            [
                                'attribute' => 'estado',
                                'format' => 'raw',
                                'value' => $model->estadoHtml,
                                'visible' => $gerirCinemas,
                            ],
                            [
                                'label' => 'Localização',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $url = "https://www.google.com/maps?q=@{$model->latitude},{$model->longitude}&hl=pt&z=15&output=embed";
                                    return "<div>
                                        <iframe src='{$url}' width='100%' height='400' class='rounded-3' allowfullscreen loading='lazy'></iframe>
                                    </div>";
                                },
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

    <?php if ($model->salas): ?>
        <h3 class="mt-4 mb-3">Salas</h3>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            <?php if ($gerirSalas && $model->isEstadoAtivo()): ?>
                                <?= Html::a('Criar Sala', ['sala/create', 'cinema_id' => $model->id], ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
                        </p>
                        <?= $this->render('_salas', [
                            'dataProvider' => $salasDataProvider,
                            'gerirSalas' => $gerirSalas,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>