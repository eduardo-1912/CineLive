<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */

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
                        'userCinemaId' => $userCinemaId,
                        'gerirUtilizadores' => $gerirUtilizadores,
                        'gerirFuncionarios' => $gerirFuncionarios,
                    ]) ?>

                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>