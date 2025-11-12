<?php

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */

$return_path = Yii::$app->user->can('gerirCinemas') ? 'index' : 'view?id=' . $currentUser->identity->profile->cinema_id;

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
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>