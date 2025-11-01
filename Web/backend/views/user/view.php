<?php

use common\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$breadcrumb = Yii::$app->user->can('gerirUtilizadores') ? 'Utilizadores' : 'Funcionários';
$return_path = $breadcrumb == 'Utilizadores' ? 'index' : 'funcionarios';
if (!Yii::$app->user->can('gerirFuncionarios')) {
    $return_path = '';
}

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
                    <p> <?php
                        $user = Yii::$app->user;
                        $isAdmin = $user->can('admin');
                        $isGerente = $user->can('gerente');
                        $isOwnAccount = ($user->id == $model->id);
                        $mesmoCinema = $isGerente && $user->identity->profile->cinema_id == $model->profile->cinema_id;

                        // EDITAR (ADMIN / PRÓPRIO UTILIZADOR)
                        if ($isAdmin || $isOwnAccount) {
                            echo Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-1']);
                        }

                        // ATIVAR/DESATIVAR (ADMIN OU GERENTE DOS SEUS FUNCIONÁRIOS)
                        if ($isAdmin || ($isGerente && $mesmoCinema && !$isOwnAccount)) {
                            if ($model->status == User::STATUS_INACTIVE || $model->status == User::STATUS_DELETED) {
                                echo Html::a('Ativar', ['activate', 'id' => $model->id], ['class' => 'btn btn-success me-1', 'data' => ['method' => 'post'],]);
                            }
                            elseif ($model->status == User::STATUS_ACTIVE) {
                                echo Html::a('Desativar', ['deactivate', 'id' => $model->id], ['class' => 'btn btn-secondary me-1', 'data' => ['method' => 'post'],]);
                            }
                        }

                        // ELIMINAR (ADMIN / GERENTES PARA FUNCIONÁRIOS DO SEU CINEMA / PRÓPRIO UTILIZADOR)
                        if ($isAdmin || $isGerente && $mesmoCinema) {
                            echo Html::a('Eliminar', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger', 'data' => ['method' => 'post'],]);
                        }
                    ?> </p>

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'id',
                                'label' => 'ID',
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
                                'label' => 'Função',
                                'value' => $model->roleFormatted,
                            ],
                            [
                                'attribute' => 'cinema_id',
                                'label' => 'Cinema',
                                'value' => $model->cinema->nome ?? '-',
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado da Conta',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    switch ($model->status) {
                                        case 10:
                                            return '<span>Ativa</span>';
                                        case 9:
                                            return '<span class="text-danger">Inativa</span>';
                                        case 0:
                                            return '<span class="text-danger">Eliminada</span>';
                                        default:
                                            return '<span class="text-secondary">Desconhecido</span>';
                                    }
                                },
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