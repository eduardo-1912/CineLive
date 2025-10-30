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
            <div class="row bg-light rounded-3 overflow-hidden mb-4 shadow-sm">
                <!-- Mapa gerado por latitude/longitude -->
                <div class="col-md-6 p-0">
                    <iframe
                            width="100%"
                            height="300"
                            style="border:0;"
                            loading="lazy"
                            allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=<?= Html::encode($cinemas->latitude) ?>,<?= Html::encode($cinemas->longitude) ?>&hl=pt&z=15&output=embed">
                    </iframe>
                </div>
                <div class="col-md-6 p-4 d-flex flex-column justify-content-center">

                        <h4 class="fw-bold mb-1"><strong><?= html::encode($cinemas->nome) ?></strong></h4><br>
                        <p class="text-muted mb-3">Gerenciado por: <?= html::encode($cinemas->gerente->nome) ?></p>

                        <h4 class="mb-2"><strong>Morada</strong></h4><br>
                        <p class="mb-2"><?= html::encode($cinemas->rua . ", " . $cinemas->codigo_postal . ", " . $cinemas->cidade) ?></p>

                        <h4><strong>Telemovel</strong></h4><br>
                        <p><?= html::encode($cinemas->telefone) ?></p>

                        <h4><strong>Email</strong></h4><br>
                        <p><?= html::encode($cinemas->email) ?></p>

                        <h4><strong>Horario</strong></h4><br>
                        <p><?= html::encode($cinemas->horario_abertura . " - " . $cinemas->horario_fecho) ?></p>

                        <h4><strong>Capacidade</strong></h4><br>
                        <p><?= html::encode($cinemas->count(\common\models\Sala::$cinema_id) . " salas - ") ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
