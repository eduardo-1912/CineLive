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

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'cliente',
                                'value' => function ($model) {
                                    if ($model->cliente && $model->cliente->profile) {
                                        return Html::a(
                                            Html::encode($model->cliente->profile->nome),
                                            ['user/view', 'id' => $model->cliente->id],
                                            ['class' => 'text-decoration-none text-primary']
                                        );
                                    }
                                    return '<span class="text-muted">[Conta eliminada]</span>';
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
                            [
                                'label' => 'Filme',
                                'format' => 'raw',
                                'value' => fn($model) => Html::a(
                                    $model->sessao->filme->titulo,
                                    ['filme/view', 'id' => $model->sessao->filme->id],
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
                            [
                                'attribute' => 'estado',
                                'value' => fn($model) => ActionColumnButtonHelper::compraEstadoDropdown($model),
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

    <?php if ($model->bilhetes): ?>
        <h3 class="mt-4 mb-3">Bilhetes</h3>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            <?php if ($model->isEstadoConfirmada() && !$model->isTodosBilhetesConfirmados() && count($model->bilhetes) > 1): ?>
                                <?= Html::a('Confirmar Todos', ['confirm-all-tickets', 'id' => $model->id], [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer confirmar todos os bilhetes desta compra?',
                                        'method' => 'post',
                                    ],
                                ]); ?>
                            <?php endif; ?>
                        </p>
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