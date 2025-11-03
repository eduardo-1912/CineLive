<?php

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
                            <?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'post']); ?>
                            <div class="d-flex align-items-start gap-1">
                                <?= $form->field($model, 'nome')->textInput(['maxlength' => true, 'placeholder' => 'Ex: Ação, Comédia...'])->label(false) ?>
                                <?= Html::submitButton('Adicionar', ['class' => 'btn btn-success', 'style' => 'height: 38px']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ],
                        'columns' => [
                            'id',
                            'nome',
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{update} {delete}',
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
