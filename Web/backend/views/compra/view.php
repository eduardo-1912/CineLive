<?php

use backend\components\ActionColumnButtonHelper;
use common\models\Compra;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Compra */

$this->title = 'Compra #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
\yii\web\YiiAsset::register($this);

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirCompras = $currentUser->can('gerirCompras');

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <?php
                        $btnClass = match ($model->estado) {
                            Compra::ESTADO_CONFIRMADA => 'btn-success',
                            Compra::ESTADO_CANCELADA => 'btn-danger disabled',
                            default => 'btn-secondary',
                        };

                        // SE A COMPRA ESTIVER CANCELADA --> DESATIVAR BOTÃO
                        if ($model->estado === Compra::ESTADO_CANCELADA): ?>
                            <div class="btn-group">
                                <button type="button" class="btn <?= $btnClass ?>">
                                    <?= Html::encode($model->displayEstado()) ?>
                                </button>
                            </div>

                        <?php else: ?>
                            <div class="btn-group">
                                <button type="button" class="btn <?= $btnClass ?> dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= Html::encode($model->displayEstado()) ?>
                                </button>

                                <ul class="dropdown-menu">
                                    <?php foreach (Compra::optsEstado() as $estado => $label): ?>
                                        <?php if ($estado === $model->estado || $estado === Compra::ESTADO_PENDENTE) continue; ?>
                                        <li>
                                            <?= Html::a($label, ['compra/change-status', 'id' => $model->id, 'estado' => $estado], [
                                                'class' => 'dropdown-item',
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => "Tem a certeza que quer alterar o estado para '{$label}'?",
                                                ],
                                            ]) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                    </div>


                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'cliente',
                                'value' => function ($model) {
                                    return Html::a($model->cliente->profile->nome, ['user/view', 'id' => $model->cliente->id]);
                                },
                                'format' => 'raw',
                            ],

                            [
                                'attribute' => 'sessao_id',
                                'label' => 'Sessão',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $bilhete = $model->getBilhetes()->one();
                                    if ($bilhete && $bilhete->sessao) {
                                        return Html::a(
                                            $bilhete->sessao->nome,
                                            ['sessao/view', 'id' => $bilhete->sessao->id],
                                            ['class' => 'text-decoration-none text-primary']
                                        );
                                    }
                                    return '-';
                                },
                            ],
                            'dataFormatada',
                            [
                                'attribute' => 'total',
                                'value' => fn($model) => $model->totalFormatado . '€',
                            ],
                            [
                                'attribute' => 'pagamento',
                                'value' => fn($model) => $model->displayPagamento(),
                            ],
                            [
                                'attribute' => 'nomeCinema',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::a($model->cinema->nome, ['cinema/view', 'id' => $model->cinema->id],
                                        ['class' => 'text-decoration-none text-primary']);
                                },
                                'visible' => $isAdmin,
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

    <?php if ($model->bilhetes): ?>
        <h3 class="mt-4 mb-3">Bilhetes</h3>

        <div class="card">
            <div class="card-body">
                <div class="row">

                    <div class="col-md-12">
                        <?= $this->render('_bilhetes', [
                            'dataProvider' => $bilhetesDataProvider,
                            'compra' => $model,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>