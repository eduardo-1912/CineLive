<?php

use backend\assets\AppAsset;
use backend\components\ActionColumnButtonHelper;
use common\models\Cinema;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\AppGridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Utilizadores';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?= Html::a('Criar Utilizador', ['create'], ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>

                    <?= AppGridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            'class' => 'yii\bootstrap5\LinkPager',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'label' => 'ID',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'username',
                            'email:email',
                            [
                                'attribute' => 'nome',
                                'value' => 'profile.nome',
                            ],
                            [
                                'attribute' => 'role',
                                'label' => 'Função',
                                'value' => 'roleFormatted',
                                'filter' => [
                                    'admin' => 'Administrador',
                                    'gerente' => 'Gerente',
                                    'funcionario' => 'Funcionário',
                                    'cliente' => 'Cliente',
                                ],
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'value' => 'cinema.nome',
                                'filter' => ArrayHelper::map(Cinema::find()->orderBy('nome')->asArray()->all(), 'id', 'nome'
                                ),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado da Conta',
                                'value' => function ($model) {
                                    switch ($model->status) {
                                        case $model::STATUS_ACTIVE: return '<span>Ativa</span>';
                                        case $model::STATUS_INACTIVE: return '<span class="text-danger">Inativa</span>';
                                        case $model::STATUS_DELETED: return '<span class="text-danger">Eliminada</span>';
                                        default: return '<span class="text-secondary">Desconhecido</span>';
                                    }
                                },
                                'format' => 'raw',
                                'filter' => [
                                    User::STATUS_ACTIVE => 'Ativa',
                                    User::STATUS_INACTIVE => 'Inativa',
                                    User::STATUS_DELETED => 'Eliminada',
                                ],
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => '{view} {update} {activate} {deactivate} {delete}',
                                'buttons' => ActionColumnButtonHelper::userButtons(),
                            ],
                        ],
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
