<?php

/* @var $this yii\web\View */
/* @var $gerirUtilizadores bool */
/* @var $criarFuncionariosCinema bool */
/* @var $isOwnAccount bool */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $cinemaOptions array */
/* @var $userCinemaId int|null */


$this->title = 'Criar ' . ($gerirUtilizadores ? 'Utilizador' : 'Funcionário');
$this->params['breadcrumbs'][] = ['label' => $gerirUtilizadores ? 'Utilizadores' : 'Funcionários', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
                        'criarFuncionariosCinema' => $criarFuncionariosCinema,
                        'isOwnAccount' => false,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>