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
        [
            'attribute' => 'preco',
            'value' => fn($bilhete) => $bilhete->preco . ' €',
        ],
        [
            'attribute' => 'lugar',
            'value' => fn($b) => $b->lugar ? $b->lugar : '-',
            'format' => 'raw',
        ],
        [
            'header' => 'Editar',
            'format' => 'raw',
            'value' => function ($bilhete) {

                $isPendente = $bilhete->estado === Bilhete::ESTADO_PENDENTE;
                $btnClass = $isPendente ? 'btn-warning' : 'btn-secondary';

                return Html::beginForm(['bilhete/update-lugar', 'id' => $bilhete->id], 'post', [
                        'class' => 'd-inline-flex gap-1 align-items-center',
                    ]) .
                    Html::input('text', 'Bilhete[lugar]', $bilhete->lugar, [
                        'class' => 'form-control form-control-sm', 'style' => 'width: 20rem', 'disabled' => !$isPendente,
                    ]) .
                    Html::submitButton('<i class="fas fa-edit"></i>', [
                        'class' => "btn btn-sm {$btnClass}",
                        'title' => 'Guardar',
                        'disabled' => !$isPendente,
                    ]) .
                    Html::endForm();
            },
            'headerOptions' => ['style' => 'width: 20rem'],
        ],
        [
            'header' => 'Estado',
            'class' => 'backend\components\AppActionColumn',
            'template' => '{changeStatus}',
            'buttons' => ActionColumnButtonHelper::bilheteButtons(),
        ],
    ],
]); ?>
