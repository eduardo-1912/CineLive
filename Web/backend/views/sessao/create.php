<?php

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */
/* @var $gerirSessoes bool */
/* @var $cinemaOptions array */
/* @var $filmeOptions array */
/* @var $salaOptions array */

$this->title = 'Criar Sessão';
$this->params['breadcrumbs'][] = ['label' => 'Sessões', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?=$this->render('_form', [
                        'model' => $model,
                        'gerirSessoes' => $gerirSessoes,
                        'cinemaOptions' => $cinemaOptions,
                        'filmeOptions' => $filmeOptions,
                        'salaOptions' => $salaOptions,
                        'hasBilhetes' => false,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>