<?php

use backend\components\LinkHelper;
use common\helpers\Formatter;
use backend\components\AppGridView;

/* @var yii\data\ActiveDataProvider $dataProvider */
/* @var $gerirSessoes bool */

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
            'attribute' => 'filme.titulo',
            'label' => 'Filme',
            'format' => 'raw',
            'value' => fn($model) => LinkHelper::simple($model->filme->titulo, 'filme/view', $model->filme->id),
            'headerOptions' => ['style' => 'width: 18rem;'],
        ],
        [
            'attribute' => 'data',
            'value' => fn($model) => Formatter::data($model->data),
        ],
        [
            'attribute' => 'hora_inicio',
            'value' => fn($model) => Formatter::hora($model->hora_inicio),
        ],
        [
            'attribute' => 'hora_fim',
            'value' => fn($model) => Formatter::hora($model->hora_fim),
        ],
        [
            'attribute' => 'numeroLugaresDisponiveis',
            'value' => fn($model) => $model->numeroLugaresDisponiveis . '/' . $model->sala->numeroLugares,
        ],
        [
            'attribute' => 'estado',
            'value' => 'estadoHtml',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width: 14rem;'],
        ],
        [
            'class' => 'backend\components\AppActionColumn',
            'template' => $gerirSessoes ? '{view} {update} {delete}' : '{view}',
            'controller' => 'sessao',
            'headerOptions' => ['style' => 'width: 1rem;'],
        ],
    ],
]); ?>