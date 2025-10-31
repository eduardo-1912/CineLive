<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$breadcrumb = Yii::$app->user->can('gerirUtilizadores') ? 'Utilizadores' : 'Funcionários';
$return_path = $breadcrumb == 'Utilizadores' ? 'index' : 'funcionarios';
if (!Yii::$app->user->can('gerirFuncionarios')) { $return_path = ''; }

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
                        <?php
                            if (Yii::$app->user->can('admin') || Yii::$app->user->id == $model->id) { ?>
                                <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>
                                <?php if (Yii::$app->user->can('admin')) {
                                    if ($model->status == 9) { ?>
                                        <?= Html::a('Ativar', ['activate', 'id' => $model->id], ['class' => 'btn btn-success', 'data' => ['method' => 'post',]]); ?>
                                    <?php }

                                    else if ($model->status == 10) { ?>
                                        <?= Html::a('Desativar', ['deactivate', 'id' => $model->id], ['class' => 'btn btn-secondary', 'data' => ['method' => 'post',]]); ?>
                                    <?php }
                                } ?>
                                <?= Html::a('Eliminar', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger', 'data' => ['method' => 'post',]]); ?>
                            <?php }
                        ?>
                    </p>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'username',
                            'email',
                            'profile.nome',
                            'roleFormatted',
                            'cinema.nome',
                            'status',
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