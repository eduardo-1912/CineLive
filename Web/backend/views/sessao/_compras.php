<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use backend\components\LinkHelper;
use common\models\Cinema;
use common\models\Compra;
use common\models\Sessao;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

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
            'value' => fn($model) => LinkHelper::cliente($model),
            'format' => 'raw',
        ],
        [
            'attribute' => 'cinema_id',
            'label' => 'Cinema',
            'format' => 'raw',
            'value' => fn($model) => LinkHelper::cinema($model->sessao),
        ],
        [
            'attribute' => 'data',
            'value' => 'dataFormatada',
        ],
        [
            'attribute' => 'total',
            'value' => 'totalEmEuros',
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
