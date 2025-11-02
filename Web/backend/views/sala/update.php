<?php

/* @var $this yii\web\View */
/* @var $model common\models\Sala */

$this->title = 'Editar: Sala ' . $model->numero;
$this->params['breadcrumbs'][] = ['label' => $model->cinema->nome, 'url' => ['cinema/view?id=' . $model->cinema_id]];
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->numero, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
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