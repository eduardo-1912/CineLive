<?php

use common\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$currentUser = Yii::$app->user;

$gerirUtilizadores = $currentUser->can('gerirUtilizadores');
$gerirFuncionarios = $currentUser->can('gerirFuncionarios') && !$currentUser->can('gerirUtilizadores');

$isOwnAccount = ($currentUser->id == $model->id);
$mesmoCinema = $gerirFuncionarios && $currentUser->identity->profile->cinema_id == $model->profile->cinema_id;

$breadcrumb = $gerirUtilizadores || $isOwnAccount ? 'Utilizadores' : 'Funcionários';
$return_path = $gerirUtilizadores || $gerirFuncionarios && !$isOwnAccount ? 'index' : 'view?id=' . $currentUser->id;

$this->title = $model->profile->nome ?? $model->username;
$this->params['breadcrumbs'][] = ['label' => $breadcrumb, 'url' => [$return_path]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <!-- EDITAR (ADMIN / PRÓPRIO UTILIZADOR) -->
                        <?php if ($gerirUtilizadores || $isOwnAccount): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']); ?>
                        <?php endif; ?>

                        <!-- ATIVAR/DESATIVAR (ADMIN OU GERENTE DOS SEUS FUNCIONÁRIOS) -->
                        <?php if ($gerirUtilizadores || $gerirFuncionarios && $mesmoCinema && !$isOwnAccount): ?>
                            <?php if ($model->status == $model::STATUS_INACTIVE || $model->status == $model::STATUS_DELETED): ?>
                                <?php $btnColor = $model->isStatusInactive() ? 'btn-success' : 'btn-primary'; ?>
                                <?= Html::a('Ativar', ['activate', 'id' => $model->id], ['class' => 'btn ' . $btnColor, 'data' => ['method' => 'post']]); ?>
                            <?php elseif ($model->status == $model::STATUS_ACTIVE): ?>
                                <?= Html::a('Desativar', ['deactivate', 'id' => $model->id], ['class' => 'btn btn-secondary', 'data' => ['method' => 'post']]); ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- ELIMINAR (ADMIN/GERENTES PARA FUNCIONÁRIOS DO SEU CINEMA) -->
                        <?php if ($gerirFuncionarios && $mesmoCinema && !$isOwnAccount): ?>
                            <?= Html::a('Eliminar', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger', 'data' => ['method' => 'post']]); ?>
                        <?php elseif ($gerirUtilizadores): ?>
                            <?= Html::a('<i class="fas fa-skull mr-1"></i> Eliminar', ['delete', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-danger',
                                    'title' => 'Eliminar permanentemente',
                                    'data' => [
                                        'confirm' => 'Tem a certeza que quer eliminar este utilizador permanentemente? Esta ação não pode ser desfeita!',
                                        'method' => 'post',
                                    ],
                                ]
                            ); ?>
                        <?php endif; ?>
                    </p>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width: 3rem;'],
                            ],
                            'username',
                            'email',
                            [
                                'attribute' => 'profile.nome',
                                'value' => $model->profile->nome ?? '-',
                            ],
                            [
                                'attribute' => 'profile.telemovel',
                                'value' => $model->profile->telemovel ?? '-',
                            ],
                            [
                                'attribute' => 'role',
                                'value' => $model->roleFormatted,
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'value' => $model->cinema->nome ?? '-',
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => fn($model) => $model->statusFormatado,
                                'visible' => $gerirUtilizadores || $gerirFuncionarios && !$isOwnAccount,
                            ],
                        ],
                    ]) ?>

                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>