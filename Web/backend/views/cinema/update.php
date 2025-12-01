<?php

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */
/* @var $gerirCinemas bool */
/* @var $estadoOptions array */

$this->title = 'Editar: ' . $model->nome;
$this->params['breadcrumbs'][] = $gerirCinemas ? ['label' => 'Cinemas', 'url' => ['index']] : ['label' => 'Cinemas'];
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
                        'gerirCinemas' => $gerirCinemas,
                        'estadoOptions' => $estadoOptions,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>