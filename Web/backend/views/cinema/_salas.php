<?php

use backend\components\ActionColumnButtonHelper;
use common\models\Cinema;
use common\models\Sala;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\components\AppGridView;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Sala $sala */

$actionColumnButtons = $gerirSalas ? '{view} {update} {activate} {close}' : '{view}';

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
        'nome',
        'num_filas',
        'num_colunas',
        'lugares',
        'precoEmEuros',
        [
            'attribute' => 'estadoFormatado',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width: 9rem;'],
        ],
        [
            'class' => 'backend\components\AppActionColumn',
            'controller' => 'sala',
            'template' => $actionColumnButtons,
            'buttons' => ActionColumnButtonHelper::salaButtons(),
            'headerOptions' => ['style' => 'width: 3rem;'],
        ],
    ],
]); ?>
