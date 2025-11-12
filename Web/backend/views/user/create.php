<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$currentUser = Yii::$app->user;
$gerirUtilizadores = $currentUser->can('gerirUtilizadores');
$gerirFuncionarios = $currentUser->can('gerirFuncionarios') && !$currentUser->can('gerirUtilizadores');

$this->title = 'Criar ' . ($gerirUtilizadores ? 'Utilizador' : 'Funcionário');
$this->params['breadcrumbs'][] = ['label' => $gerirUtilizadores ? 'Utilizadores' : 'Funcionários', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'profile' => $profile,
                        'cinemasOptions' => $cinemasOptions,
                    ]) ?>

                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>