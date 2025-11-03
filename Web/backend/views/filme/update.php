<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */

$this->title = 'Editar: ' . $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Filmes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->titulo, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>
