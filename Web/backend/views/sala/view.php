<?php

use common\models\Sala;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirSalas = $currentUser->can('gerirSalas');

$this->title = 'Sala ' . $model->numero;
$this->params['breadcrumbs'][] = ['label' => $model->cinema->nome, 'url' => ['cinema/view?id=' . $model->cinema_id]];
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->numero;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if($gerirSalas): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                            <?php if($model->estado == $model::ESTADO_ATIVA): ?>
                                <?= Html::a('Encerrar', ['deactivate', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer encerrar esta sala?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php elseif($model->estado == $model::ESTADO_ENCERRADA): ?>
                                <?= Html::a('Ativar', ['activate', 'id' => $model->id], [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer ativar esta sala?',
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
                                'label' => 'Nome',
                                'attribute' => 'numero',
                                'value' => function ($model) {
                                    return 'Sala ' . $model->numero;
                                },
                            ],
                            'num_filas',
                            'num_colunas',
                            [
                                'attribute' => 'lugares',
                                'value' => function ($model) {
                                    return $model->num_filas * $model->num_colunas;
                                },
                            ],
                            [
                                'attribute' => 'preco_bilhete',
                                'value' => function ($model) {
                                    return $model->preco_bilhete . '€';
                                },
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'value' => function ($model) {
                                    return $model->cinema->nome;
                                },
                                'visible' => $isAdmin,
                            ],
                            [
                                'attribute' => 'estado',
                                'format' => 'raw',
                                'value' => fn($model) => $model->estadoFormatado,
                                'visible' => $gerirSalas,
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
</div>