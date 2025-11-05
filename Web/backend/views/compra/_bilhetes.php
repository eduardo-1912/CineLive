<?php

use backend\components\ActionColumnButtonHelper;
use yii\helpers\Html;
use backend\components\AppGridView;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Compra $compra */

?>

<?= AppGridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'columns' => [
        [
            'attribute' => 'id',
            'headerOptions' => ['style' => 'width: 4rem;'],
        ],
        [
            'label' => 'Filme',
            'value' => function ($bilhete) {
                return Html::a(
                    Html::encode($bilhete->sessao->filme->titulo),
                    ['filme/view', 'id' => $bilhete->sessao->filme->id],
                    ['class' => 'text-decoration-none text-primary']
                );
            },
            'format' => 'raw',
        ],
        [
            'label' => 'Sessão',
            'value' => function ($bilhete) {
                $sessao = $bilhete->sessao;
                $hora = Yii::$app->formatter->asTime($sessao->hora_inicio, 'HH:mm');
                return Html::a(
                    "Sessão {$sessao->id} ({$hora})",
                    ['sessao/view', 'id' => $sessao->id],
                    ['class' => 'text-decoration-none']
                );
            },
            'format' => 'raw',
        ],
        [
            'label' => 'Cinema',
            'value' => fn($bilhete) => $bilhete->sessao->cinema->nome ?? '—',
        ],
        'lugar',
        [
            'attribute' => 'preco',
            'value' => fn($bilhete) => $bilhete->preco . ' €',
        ],
        [
            'attribute' => 'estado',
            'value' => fn($bilhete) => ucfirst($bilhete->estado),
        ],
        [
            'class' => 'backend\components\AppActionColumn',
            'template' => '{changeStatus}',
            'buttons' => ActionColumnButtonHelper::compraButtons(),
            'headerOptions' => ['style' => 'width: 2rem;'],
        ],
    ],
]); ?>
