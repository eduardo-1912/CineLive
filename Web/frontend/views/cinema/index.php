<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Cinema[] $cinemas */

$this->title = 'Cinemas';
?>

<div class="container my-5">
    <h1 class="mb-4">Os Nossos Cinemas</h1>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($cinemas as $cinema): ?>
            <div class="col">
                <div class=" col-6 color-blue ">

                </div>
                <div class="col-6 color-red">
                    <div>
                        <h3><strong><?= html::encode($cinemas->nome) ?></strong></h3>
                        <p>Gerenciado por: <?= html::encode($cinemas->gerente->nome) ?></p>
                    </div>
                    <div class="align-content-start">
                        <h3><strong>Morada</strong></h3>
                        <p><?= html::encode($cinemas->rua . ", " . $cinema->codigo_postal . ", " . $cinema->cidade) ?></p>
                    </div>
                    <div class="align-content-start">
                        <h3><strong>Telemovel</strong></h3>
                        <p><?= html::encode($cinemas->telefone) ?></p>
                    </div>
                    <div class="">
                        <h3><strong>Email</strong></h3>
                        <p><?= html::encode($cinemas->email) ?></p>
                    </div>
                    <div class="">
                        <h3><strong>Horario</strong></h3>
                        <p><?= html::encode($cinemas->horario_abertura . " - " . $cinemas->horario_fecho) ?></p>
                    </div>
                    <div class="">
                        <h3><strong>Capacidade</strong></h3>
                        <p><?= html::encode($cinemas->count(\common\models\Sala::$cinema_id) . " salas - ") ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
