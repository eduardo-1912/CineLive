<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */

$currentUser = Yii::$app->user;
$userCinema = $currentUser->identity->profile->cinema;
$isAdmin = $currentUser->can('admin');

$this->title = 'Criar Sala';
$this->params['breadcrumbs'][] = ['label' => $isAdmin ? 'Cinemas' : $userCinema->nome, 'url' => [$isAdmin ? 'cinema/index' : ('cinema/view?id=' . $userCinema->id)]];
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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