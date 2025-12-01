<?php

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $gerirSalas bool */
/* @var $gerirSalasCinema bool */
/* @var $userCinema common\models\Cinema|null */
/* @var $proximoNumero int|null */
/* @var $cinemaOptions array */


$this->title = 'Criar Sala';
$this->params['breadcrumbs'][] = ['label' => $gerirSalas ? 'Cinemas' : $userCinema->nome, 'url' => [$gerirSalas ? 'cinema/index' : ('cinema/view?id=' . $userCinema->id)]];
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
                        'gerirSalas' => $gerirSalas,
                        'gerirSalasCinema' => $gerirSalasCinema,
                        'userCinemaId' => $userCinema->id ?? null,
                        'cinemaOptions' => $cinemaOptions,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>