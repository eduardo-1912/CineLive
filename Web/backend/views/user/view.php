<?php

use backend\components\ActionColumnButtonHelper;
use common\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$currentUser = Yii::$app->user;
$isOwnAccount = ($currentUser->id == $model->id);
$gerirUtilizadores = $currentUser->can('gerirUtilizadores');
$gerirFuncionarios = $currentUser->can('gerirFuncionarios');

$label = $gerirUtilizadores || $isOwnAccount ? 'Utilizadores' : 'Funcionários';
$return_path = $gerirUtilizadores || $gerirFuncionarios && !$isOwnAccount ? 'index' : 'view?id=' . $currentUser->id;

$this->title = $model->profile->nome ?? $model->username;
$this->params['breadcrumbs'][] = ['label' => $label, 'url' => [$return_path]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php if ($gerirUtilizadores || $isOwnAccount): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']); ?>
                        <?php endif; ?>

                        <?php if ($gerirUtilizadores && !$isOwnAccount): ?>
                            <?= Html::a('Eliminar', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger', 'data' => ['method' => 'post']]); ?>
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
                            'email:email',
                            [
                                'attribute' => 'profile.nome',
                                'value' => $model->profile->nome ?? '-',
                            ],
                            [
                                'attribute' => 'profile.telemovel',
                                'value' => $model->profile->telemovel ?? '-',
                            ],
                           'roleFormatted',
                            [
                                'attribute' => 'cinema_id',
                                'value' => $model->cinema->nome ?? '-',
                                'visible' => $model->isStaff(),
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function ($model) { return ActionColumnButtonHelper::userEstadoDropdown($model); },
                                'format' => 'raw',
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