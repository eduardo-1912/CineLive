<?php

/* @var $this yii\web\View */
/* @var $model common\models\AluguerSala */
/* @var bool $gerirAlugueres */
/* @var bool $gerirAlugueresCinema */
/* @var array $salaOptions */
/* @var array $estadoOptions */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Alugueres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'gerirAlugueres' => $gerirAlugueres,
                        'gerirAlugueresCinema' => $gerirAlugueresCinema,
                        'salaOptions' => $salaOptions,
                        'estadoOptions' => $estadoOptions,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>