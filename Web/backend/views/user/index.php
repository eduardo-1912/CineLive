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

$currentUser = Yii::$app->user;
$gerirUtilizadores = $currentUser->can('gerirUtilizadores');
$gerirFuncionarios = $currentUser->can('gerirFuncionarios') && !$currentUser->can('gerirUtilizadores');

$actionColumnButtons = $gerirUtilizadores
    ? '{view} {update} {activate} {deactivate} {delete}'
    : '{view} {activate} {deactivate} {softDelete}';

$title = $gerirUtilizadores ? 'Utilizadores' : 'Funcionários';

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php if($gerirUtilizadores || $gerirFuncionarios): ?>
                                <?= Html::a('Criar ' . ($gerirUtilizadores ? 'Utilizador' : 'Funcionário'), ['create'], ['class' => 'btn btn-success']) ?>
                            <?php endif; ?>
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
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'username',
                            'email:email',
                            [
                                'attribute' => 'nome',
                                'value' => 'profile.nome',
                            ],
                            [
                                'label' => 'Telemóvel',
                                'attribute' => 'telemovel',
                                'value' => 'profile.telemovel',
                                'visible' => $gerirFuncionarios,
                            ],
                            [
                                'attribute' => 'role',
                                'value' => 'roleFormatted',
                                'filter' => [
                                    'admin' => 'Administrador',
                                    'gerente' => 'Gerente',
                                    'funcionario' => 'Funcionário',
                                    'cliente' => 'Cliente',
                                ],
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $gerirUtilizadores,
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'value' => 'cinema.nome',
                                'filter' => ArrayHelper::map(Cinema::find()->orderBy('nome')->asArray()->all(), 'id', 'nome'),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'visible' => $gerirUtilizadores,
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function ($model) {
                                    switch ($model->status) {
                                        case $model::STATUS_ACTIVE: return '<span>Ativo</span>';
                                        case $model::STATUS_INACTIVE: return '<span class="text-danger">Inativo</span>';
                                        case $model::STATUS_DELETED: return '<span class="text-danger">Eliminado</span>';
                                        default: return '<span class="text-secondary">Desconhecido</span>';
                                    }
                                },
                                'format' => 'raw',
                                'filter' => $gerirUtilizadores
                                ? // SE FOR ADMIN --> COM ACESSO A UTILIZADORES ELIMINADOS (SOFT-DELETED)
                                [
                                    User::STATUS_ACTIVE => 'Ativo',
                                    User::STATUS_INACTIVE => 'Inativo',
                                    User::STATUS_DELETED => 'Eliminado',
                                ]
                                : // SE FOR GERENTE --> SEM ACESSO A UTILIZADORES ELIMINADOS (SOFT-DELETED)
                                [
                                    User::STATUS_ACTIVE => 'Ativo',
                                    User::STATUS_INACTIVE => 'Inativo',
                                ],
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos',],
                            ],
                            [
                                'class' => 'backend\components\AppActionColumn',
                                'template' => $actionColumnButtons,
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
