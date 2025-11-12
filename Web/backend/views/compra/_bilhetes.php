<?php

use backend\components\ActionColumnButtonHelper;
use common\models\Bilhete;
use yii\helpers\Html;
use backend\components\AppGridView;

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
        'precoEmEuros',
        [
            'attribute' => 'lugar',
            'value' => 'lugar' ?? '-',
            'format' => 'raw',
        ],
        [
            'header' => 'Editar',
            'format' => 'raw',
            'value' => function ($bilhete) {

                $btnClass = !$bilhete->isEditable() ? 'btn-warning' : 'btn-secondary';

                return Html::beginForm(['bilhete/update-lugar', 'id' => $bilhete->id], 'post', [
                        'class' => 'd-inline-flex gap-1 align-items-center',
                    ]) .
                    Html::input('text', 'Bilhete[lugar]', $bilhete->lugar, [
                        'class' => 'form-control form-control-sm', 'style' => 'width: 20rem', 'disabled' => !$bilhete->isEditable(),
                    ]) .
                    Html::submitButton('<i class="fas fa-edit"></i>', [
                        'class' => "btn btn-sm {$btnClass}",
                        'title' => 'Guardar',
                        'disabled' => !$bilhete->isEditable(),
                    ]) .
                    Html::endForm();
            },
            'headerOptions' => ['style' => 'width: 20rem'],
        ],
        [
            'header' => 'Estado',
            'class' => 'backend\components\AppActionColumn',
            'template' => '{changeStatus}',
            'buttons' => ActionColumnButtonHelper::bilheteEstadoDropdown(),
        ],
    ],
]); ?>
