<?php

/* @var yii\web\View $this */
/* @var common\models\AluguerSala $model */
/* @var array $cinemaOptions */
/* @var array $salaOptions */

$this->title = 'Alugar Sala';

?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0">Aluga uma Sala</h4>
        <p class="text-muted">Envia-nos um pedido de aluguer de sala privada e um gerente entrará em contacto contigo.</p>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'cinemaOptions' => $cinemaOptions,
        'salaOptions' => $salaOptions
    ]) ?>

</div>
