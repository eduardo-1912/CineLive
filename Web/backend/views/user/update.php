<?php

/* @var $this yii\web\View */
/* @var $gerirUtilizadores bool */
/* @var $isOwnAccount bool */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $cinemaOptions array */
/* @var $userCinemaId int|null */

$this->title = 'Editar: ' . $model->profile->nome;
$this->params['breadcrumbs'][] = $gerirUtilizadores ? ['label' => 'Utilizadores', 'url' => ['index']] : ['label' => 'Utilizadores'];
$this->params['breadcrumbs'][] = ['label' => $model->profile->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'profile' => $profile,
                        'cinemaOptions' => $cinemaOptions,
                        'userCinemaId' => $userCinemaId,
                        'gerirUtilizadores' => $gerirUtilizadores,
                        'isOwnAccount' => $isOwnAccount,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>