<?php

use common\helpers\Formatter;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $gerirSalas bool */
/* @var $gerirSessoes bool */
/** @var yii\data\ActiveDataProvider $sessoesDataProvider */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => $model->cinema->nome, 'url' => ['cinema/view?id=' . $model->cinema_id]];
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => 'index'];
$this->params['breadcrumbs'][] = $model->numero;

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if ($gerirSalas): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                            <?php if ($model->isClosable()): ?>
                                <?= Html::a('Encerrar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ENCERRADA], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer encerrar esta sala?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php elseif ($model->isActivatable()): ?>
                                <?= Html::a('Ativar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ATIVA], [
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
                            'nome',
                            'num_filas',
                            'num_colunas',
                            'numeroLugares',
                            [
                                'attribute' => 'preco_bilhete',
                                'value' => fn($model) => Formatter::preco($model->preco_bilhete)
                            ],
                            [
                                'attribute' => 'estadoHtml',
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

    <?php if ($model->sessoes): ?>
        <h3 class="mt-4 mb-3">Sessões</h3>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            <?php if ($gerirSessoes): ?>
                                <?= Html::a('Criar Sessão', ['sessao/create', 'cinema_id' => $model->cinema_id], ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
                        </p>
                        <?= $this->render('_sessoes', [
                            'dataProvider' => $sessoesDataProvider,
                            'gerirSessoes' => $gerirSessoes,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>