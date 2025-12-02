<?php

use backend\components\AppGridView;
use backend\helpers\ActionColumnButtonHelper;
use common\helpers\Formatter;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Compra $compra */

?>

<?= AppGridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id',
            'headerOptions' => ['style' => 'width: 4rem'],
        ],
        [
            'attribute' => 'codigo',
        ],
        [
            'attribute' => 'preco',
            'value' => fn($model) => Formatter::preco($model->preco),
        ],        [
            'attribute' => 'lugar',
            'value' => 'lugar' ?? '-',
            'format' => 'raw',
        ],
        [
            'header' => 'Editar',
            'format' => 'raw',
            'value' => fn($model) => $this->render('_formInlineBilhete', ['model' => $model]),
            'headerOptions' => ['style' => 'width: 10rem'],
        ],
        [
            'header' => 'Estado',
            'class' => 'backend\components\AppActionColumn',
            'template' => '{changeStatus}',
            'buttons' => ActionColumnButtonHelper::bilheteEstadoDropdown(),
        ],
    ],
]); ?>
