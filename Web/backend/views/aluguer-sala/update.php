<?php

/* @var $this yii\web\View */
/* @var $model common\models\AluguerSala */

$this->title = 'Update Aluguer Sala: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Aluguer Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->render('_form', [
                        'model' => $model
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>