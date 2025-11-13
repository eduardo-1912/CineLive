<?php

use backend\components\ActionColumnButtonHelper;
use backend\components\AppGridView;
use backend\components\AppActionColumn;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\GeneroSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => 'Filmes', 'url' => ['filme/index']];
$this->title = 'Géneros';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg">
                            <?= $this->render('_create', ['model' => $model]) ?>
                        </div>
                    </div>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem'],
                            ],
                            'nome',
                            [
                                'header' => 'Editar',
                                'attribute' => 'nome',
                                'class' => 'yii\grid\DataColumn',
                                'format' => 'raw',
                                'value' => fn($model) => $this->render('_update', ['model' => $model]),
                                'headerOptions' => ['style' => 'width: 20rem'],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{delete}',
                                'headerOptions' => ['style' => 'width: 3rem'],
                            ],
                        ]
                    ]); ?>

                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
