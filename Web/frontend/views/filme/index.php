<?php

use common\models\Cinema;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme[] $filmes */

$this->title = 'Filmes';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4" style="min-height: 35px;">
        <h4 class="page-title m-0"><?= Html::encode($this->title) ?></h4>

        <div class="d-flex gap-1 align-items-center">
            <div class="d-flex">
                <!-- TOGGLE PARA 'KIDS' OU 'EM EXIBIÇÃO' -->
                <div class="d-inline-flex me-3">
                    <?php if ($estado !== 'brevemente'): ?>
                        <form method="get" action="<?= Url::to(['filme/index']) ?>" class="d-inline-flex align-items-center">

                            <!-- CINEMA ESCOLHIDO -->
                            <?= Html::hiddenInput('cinema_id', $cinema_id) ?>

                            <div class="form-check form-switch m-0 ps-0">
                                <?= Html::checkbox('estado', $estado === 'kids', [
                                    'value' => 'kids',
                                    'class' => 'form-check-input',
                                    'id' => 'kidsSwitch',
                                    'onchange' => 'this.form.submit()'
                                ]) ?>
                                <label class="form-check-label fw-semibold fs-14" for="kidsSwitch">Kids</label>
                            </div>

                        </form>
                    <?php endif; ?>
                </div>

                <!-- EM EXIBIÇÃO -->
                <a class="d-none d-sm-flex btn btn-sm btn-estado-filme <?= $estado !== 'brevemente' ? 'active' : '' ?>"
                   href="<?= Url::to(['filme/index', 'cinema_id' => $cinema_id]) ?>">Em Exibição</a>

                <!-- BREVEMENTE -->
                <a class="d-none d-sm-flex btn btn-sm btn-estado-filme <?= $estado === 'brevemente' ? 'active' : '' ?>"
                   href="<?= Url::to(['filme/index', 'estado' => 'brevemente', 'q' => $q]) ?>">Brevemente</a>
            </div>


            <!-- DROPDOWN DE CINEMAS -->
            <div class="dropdown-center <?= ($estado === 'brevemente' ? 'd-block d-sm-none' : 'd-block') ?>">
                <button class="btn btn-sm dropdown-toggle fw-medium" type="button" id="dropdownCinema" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= $currentCinema ?? 'Brevemente' ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownCinema">
                    <li class="d-block d-sm-none">
                        <a class="dropdown-item <?= $estado === 'brevemente' ? 'active' : '' ?>"
                           href="<?= Url::to(['filme/index', 'estado' => 'brevemente', 'q' => $q]) ?>">Brevemente</a>
                    </li>
                    <li class="dropdown-divider d-block d-sm-none"></li>
                    <?php foreach ($cinemas as $cinema): ?>
                        <li>
                            <a class="dropdown-item <?= $cinema_id == $cinema->id ? 'active' : '' ?>"
                               href="<?= Url::to(['filme/index', 'cinema_id' => $cinema->id, 'q' => $q]) ?>">
                                <?= $cinema->nome ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>

    <!-- CARD DE FILMES -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        <?php if ($filmes): ?>
            <?php foreach ($filmes as $filme): ?>
                <div class="col">
                    <div class="h-100 border-0">
                        <a href="<?= Url::to(['filme/view', 'id' => $filme->id, 'cinema_id' => $cinema_id]) ?>"
                           class="card-filme text-center text-decoration-none text-black d-flex flex-column gap-1">
                            <?= Html::img($filme->getPosterUrl(), [
                                'class' => 'card-img-top shadow-sm rounded-4',
                                'alt' => $filme->titulo,
                                'style' => 'object-fit: cover; aspect-ratio: 2/3;'
                            ]) ?>
                            <h5 class="fw-semibold fs-6"><?= $filme->titulo ?></h5>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="d-flex justify-content-center align-items-center w-100" style="height: 50vh;">
                <h4 class="text-muted text-center fw-semibold m-0">Nenhum filme encontrado!</h4>
            </div>
        <?php endif; ?>
    </div>
</div>
