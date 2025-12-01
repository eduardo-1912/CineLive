<?php

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $gerirSalas bool */
/* @var $gerirSalasCinema bool */
/* @var $userCinema common\models\Cinema|null */
/* @var $cinemaOptions array */

$this->title = "Editar: {$model->nome}";
$this->params['breadcrumbs'][] = ['label' => $model->cinema->nome, 'url' => ['cinema/view?id=' . $model->cinema_id]];
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index', 'cinema_id' => $model->cinema_id]];
$this->params['breadcrumbs'][] = ['label' => $model->numero, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->render('_form', [
                        'model' => $model,
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