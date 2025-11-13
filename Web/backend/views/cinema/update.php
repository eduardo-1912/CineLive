<?php

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */

$return_path = $gerirCinemas ? 'index' : 'view?id=' . $userCinemaId;

$this->title = 'Editar: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Cinemas', 'url' => [$return_path]];
$this->params['breadcrumbs'][] = ['label' => $model->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->render('_form', [
                        'model' => $model,
                        'dropdownEstados' => $dropdownEstados,
                        'gerirCinemas' => $gerirCinemas,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>