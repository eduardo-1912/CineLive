<?php

use backend\components\ActionColumnButtonHelper;
use common\models\Cinema;
use common\models\Sala;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\components\AppGridView;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Sala $sala */

$actionColumnButtons = Yii::$app->user->can('gerirSessoes') ? '{view} {update} {delete}' : '{view}';

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
            'attribute' => 'tituloFilme',
            'label' => 'Filme',
            'format' => 'raw',
            'value' => fn($model) =>
            Html::a($model->filme->titulo,
                ['filme/view', 'id' => $model->filme_id],
                ['class' => 'text-decoration-none text-primary']),
            'headerOptions' => ['style' => 'width: 18rem;'],
        ],
        [
            'attribute' => 'data',
            'value' => 'dataFormatada',
        ],
        [
            'attribute' => 'hora_inicio',
            'value' => 'horaInicioFormatada',
        ],
        [
            'attribute' => 'hora_fim',
            'value' => 'horaFimFormatada',
        ],
        [
            'label' => 'Lugares Disponíveis',
            'attribute' => 'lugaresDisponiveis',
            'value' => fn($model) =>
                $model->numeroLugaresDisponiveis . '/' . $model->sala->lugares,
        ],
        [
            'attribute' => 'estado',
            'value' => 'estadoFormatado',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width: 14rem;'],
        ],
        [
            'class' => 'backend\components\AppActionColumn',
            'template' => $actionColumnButtons,
            'controller' => 'sessao',
            'headerOptions' => ['style' => 'width: 1rem;'],
        ],
    ],
]); ?>