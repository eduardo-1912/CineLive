<?php

use backend\components\ActionColumnButtonHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Compra */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                        <?php endif; ?>
                        <?= Html::a('Sessão', ['sessao/view', 'id' => $model->getBilhetes()->one()->sessao->id], ['class' => 'btn btn-success']) ?>
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
                                'attribute' => 'estado',
                                'value' => fn($model) => $model->estadoFormatado,
                                'format' => 'raw',
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