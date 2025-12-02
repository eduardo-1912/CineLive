<?php

use backend\components\AppGridView;
use backend\helpers\ActionColumnButtonHelper;
use backend\helpers\LinkHelper;
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
            'attribute' => 'cinema_id',
            'label' => 'Cinema',
            'format' => 'raw',
            'value' => fn($model) => LinkHelper::simple($model->sessao->cinema->nome, 'cinema/view', $model->sessao->cinema->id),
        ],
        [
            'attribute' => 'sessao_id',
            'format' => 'raw',
            'value' => fn($model) => LinkHelper::simple($model->sessao->nome, 'sessao/view', $model->sessao->id),

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
