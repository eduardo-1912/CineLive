<?php

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$return_path = $isAdmin ? 'index' : 'view?id=' . $currentUser->identity->profile->cinema_id;

$this->title = 'Editar: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Cinemas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nome, 'url' => ['view', 'id' => $model->id]];
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