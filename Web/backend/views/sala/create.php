<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */


$this->title = 'Criar Sala';
$this->params['breadcrumbs'][] = ['label' => $gerirCinemas ? 'Cinemas' : $userCinema->nome, 'url' => [$gerirCinemas ? 'cinema/index' : ('cinema/view?id=' . $userCinema->id)]];
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->render('_form', [
                        'model' => $model,
                        'proximoNumero' => $proximoNumero,
                        'gerirCinemas' => $gerirCinemas,
                        'gerirSalas' => $gerirSalas,
                        'userCinemaId' => $userCinema->id ?? null,
                        'cinemasOptions' => $cinemasOptions,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>