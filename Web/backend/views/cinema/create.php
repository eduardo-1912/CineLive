<?php

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */
/* @var $gerirCinemas bool */
/* @var $estadoOptions array */

$this->title = 'Criar Cinema';
$this->params['breadcrumbs'][] = ['label' => 'Cinemas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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