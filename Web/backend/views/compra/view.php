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
                    <p>
                        <?php if ($gerirCompras): ?>

                            <?php if ($model->estado === $model::ESTADO_CONFIRMADA): ?>
                                <?= Html::a('Cancelar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_CANCELADA], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer cancelar esta compra?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php elseif ($model->estado === $model::ESTADO_CANCELADA): ?>
                                <?= Html::a('Confirmar', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_CONFIRMADA], [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer confirmar esta compra?',
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
                                'value' => fn($model) => Html::a(
                                    $model->sessao->nome,
                                    ['sessao/view', 'id' => $model->sessao->id],
                                    ['class' => 'text-decoration-none text-primary']
                                ),
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