<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use backend\components\LinkHelper;
use common\helpers\Formatter;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<?= AppGridView::widget([
    'dataProvider' => $dataProvider,
    'pager' => [
        'class' => 'yii\bootstrap4\LinkPager',
    ],
    'columns' => [
        [
            'attribute' => 'id',
            'headerOptions' => ['style' => 'width: 3rem;'],
        ],
        [
            'attribute' => 'nomeCliente',
            'label' => 'Cliente',
            'value' => fn($model) => LinkHelper::condition($model->cliente->profile->nome, 'user/view', $model->cliente_id, 'Conta eliminada'),
            'format' => 'raw',
        ],
        [
            'attribute' => 'data',
            'value' => fn($model) => Formatter::data($model->data),
        ],
        [
            'attribute' => 'total',
            'value' => fn($model) => Formatter::preco($model->total),
        ],
        'numeroBilhetes',
        'lugares',
        [
            'attribute' => 'estado',
            'label' => 'Estado',
            'format' => 'raw',
            'value' => fn($model) => ActionColumnButtonHelper::compraEstadoDropdown($model),
            'headerOptions' => ['style' => 'width: 9rem'],
        ],

        [
            'class' => 'backend\components\AppActionColumn',
            'template' => '{view} {cancel} {confirm} {confirmarBilhetes}',
            'controller' => 'compra',
            'buttons' => ActionColumnButtonHelper::compraButtons(),
            'headerOptions' => ['style' => 'width: 3rem'],
        ],
    ],
]); ?>
