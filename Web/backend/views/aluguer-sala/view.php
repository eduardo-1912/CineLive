<?php

/* @var $this yii\web\View */
/* @var $model common\models\AluguerSala */

$this->title = 'Aluguer #' . $model->id;
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
                        'nomeCliente' => $nomeCliente,
                        'emailCliente' => $emailCliente,
                        'telemovelCliente' => $telemovelCliente,
                        'nomeCinema' => $nomeCinema,
                        'salasDisponiveis' => $salasDisponiveis,
                    ]) ?>

                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>