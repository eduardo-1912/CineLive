<?php
use yii\helpers\Html;
use common\models\Sala;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Cinema[] $cinemas */

$this->title = 'Cinemas';
?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0">Os nossos cinemas</h4>
    </div>

    <!-- CINEMAS -->
    <?php foreach ($cinemas as $cinema): ?>
        <div class="box-gray p-0 overflow-hidden mb-4 shadow-sm border">
            <div class="row row-cols-1 row-cols-lg-2">

                <!-- MAPA -->
                <div>
                    <iframe
                        width="100%" height="100%" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?= $cinema->latitude ?>,<?= $cinema->longitude ?>&hl=pt&z=15&output=embed">
                    </iframe>
                </div>

                <!-- DADOS DO CINEMA -->
                <div class="px-4 px-lg-2 py-4 d-flex flex-column justify-content-center">
                    <a href="<?= Url::to(['filme/index', 'cinema_id' => $cinema->id]) ?>" class="fw-bold fs-4 link-dark text-decoration-none mb-1"><?= $cinema->nome ?></a>

                    <?php if ($cinema->gerente->profile->nome): ?>
                        <p class="text-muted mb-4">Gerido por <span class="fw-medium"><?= $cinema->gerente->profile->nome ?></span></p>
                    <?php endif; ?>

                    <div class="row row-cols-2 w-100 gy-3 mb-0 mb-lg-4">

                        <div class="col-12 d-flex flex-column text-start">
                            <span class="fw-medium fs-14">Morada</span>
                            <span class="text-muted"><?= $cinema->moradaCompleta ?></span>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-14">Telefone</span>
                            <span class="text-muted"><?= $cinema->telefone ?></span>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-14">Horário</span>
                            <span class="text-muted"><?= $cinema->horario ?></span>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-14">Email</span>
                            <a href="mailto:<?= $cinema->email ?>" target="_blank" class="text-decoration-none"><?= $cinema->email ?></a>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-14">Capacidade</span>
                            <span class="text-muted"><?= count($cinema->salas) ?> Salas<span class="d-none d-sm-inline"> • <?= $cinema->numeroLugares ?> Lugares</span></span>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    <?php endforeach; ?>

</div>
