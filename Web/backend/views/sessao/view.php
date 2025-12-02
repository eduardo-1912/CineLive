<?php

use backend\helpers\LinkHelper;
use common\helpers\Formatter;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */
/* @var $gerirSessoes bool */
/* @var $gerirSessoesCinema bool */
/* @var $mapaLugares array */
/* @var $comprasDataProvider yii\data\ActiveDataProvider */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if($gerirSessoes || $gerirSessoesCinema): ?>

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
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->filme->titulo, 'filme/view', $model->filme_id),
                            ],
                            [
                                'label' => 'Cinema',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->cinema->nome, 'cinema/view', $model->cinema_id),
                                'visible' => $gerirSessoes,
                            ],
                            [
                                'label' => 'Sala',
                                'format' => 'raw',
                                'value' => fn($model) => LinkHelper::simple($model->sala->nome, 'sala/view', $model->sala_id),
                            ],
                            [
                                'label' => 'Data',
                                'value' => fn($model) => Formatter::data($model->data),
                            ],
                            'horario',
                            [
                                'label' => 'Lugares Disponíveis',
                                'attribute' => 'lugaresDisponiveis',
                                'value' => fn($model) => $model->numeroLugaresDisponiveis . '/' . $model->sala->numeroLugares,
                            ],
                            [
                                'label' => 'Estado',
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

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_mapaLugares', [
                        'model' => $model,
                        'mapaLugares' => $mapaLugares,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($model->compras): ?>
        <h3 class="mt-4 mb-3">Compras</h3>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= $this->render('_compras', [
                            'dataProvider' => $comprasDataProvider,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>