<?php

use backend\components\AppGridView;
use backend\helpers\ActionColumnButtonHelper;
use common\helpers\Formatter;

/** @var bool $gerirSalas */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Sala $sala */

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
        'numeroLugares',
        [
            'attribute' => 'preco_bilhete',
            'value' => fn($model) => Formatter::preco($model->preco_bilhete)
        ],
        [
            'attribute' => 'estadoHtml',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width: 9rem;'],
        ],
        [
            'class' => 'backend\components\AppActionColumn',
            'controller' => 'sala',
            'template' => $gerirSalas ? '{view} {update} {activate} {close}' : '{view}',
            'buttons' => ActionColumnButtonHelper::salaButtons(),
            'headerOptions' => ['style' => 'width: 3rem;'],
        ],
    ],
]); ?>
