<?php

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Editar: ' . $model->profile->nome;
$this->params['breadcrumbs'][] = ['label' => 'Utilizadores', 'url' => [$gerirUtilizadores ? 'index' : ('view?id=' . $currentUser->id)]];
$this->params['breadcrumbs'][] = ['label' => $model->profile->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
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