<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $totalFilmes */
/** @var int $totalAlugueres */
/** @var int $totalSessoes */

?>

<div class="row row-cols-1 row-cols-sm-3 g-3 text-start">
    <div class="col">
        <div class="small-box bg-info p-0 mb-0">
            <div class="inner d-flex flex-column align-items-start">
                <h3><?= $totalFilmes ?></h3>
                <p>Filmes em exibição</p>
            </div>
            <div class="icon">
                <i class="fas fa-film"></i>
            </div>
            <a href="<?= Url::to(['/filme/index']) ?>" class="small-box-footer">
                Ver Filmes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col">
        <div class="small-box bg-danger p-0 mb-0">
            <div class="inner d-flex flex-column align-items-start">
                <h3><?= $totalAlugueres ?></h3>
                <p>Pedidos de aluguer pendentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="<?= Url::to(['/aluguer-sala/index']) ?>" class="small-box-footer">
                Ver Alugueres <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col">
        <div class="small-box bg-warning p-0 mb-0">
            <div class="inner d-flex flex-column align-items-start">
                <h3><?= $totalSessoes ?></h3>
                <p>Sessões agendadas para hoje</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="<?= Url::to(['/sessao/index']) ?>" class="small-box-footer">
                Ver Sessões <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

