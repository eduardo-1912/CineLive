<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme[] $filmes */
/** @var common\models\Cinema[] $cinemas */
/** @var int $cinema_id */
/** @var string $filter */
/** @var string $q */


$this->title = 'Filmes';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4" style="min-height: 35px;">
        <h4 class="page-title m-0"><?= $q ? 'Resultados da pesquisa': $this->title ?></h4>

        <!-- Filtros -->
        <div class="d-flex gap-1 align-items-center">
            <div class="d-flex align-items-center">

                <?php if ($q): ?>
                    <a href="<?= Url::to(['filme/index', 'cinema_id' => $cinema_id, 'filter' => $filter]) ?>" class="text-muted fs-14">Limpar</a>
                <?php endif; ?>

                <!-- Switch kids -->
                <div class="d-inline-flex me-3">
                    <?php if ($filter !== 'brevemente' && $q == null): ?>
                        <form method="get" action="<?= Url::to(['filme/index']) ?>" class="d-inline-flex align-items-center">

                            <!-- Cinema selecionado -->
                            <?= Html::hiddenInput('cinema_id', $cinema_id) ?>

                            <div class="form-check form-switch m-0 ps-0">
                                <?= Html::checkbox('filter', $filter, [
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

                <!-- Botão Em exibição -->
                <a class="d-none d-sm-flex btn btn-sm btn-estado-filme <?= $filter !== 'brevemente' ? 'active' : '' ?>"
                   href="<?= Url::to(['filme/index', 'cinema_id' => $cinema_id, 'q' => $q]) ?>">Em Exibição</a>

                <!-- Botão Brevemente -->
                <a class="d-none d-sm-flex btn btn-sm btn-estado-filme <?= $filter === 'brevemente' ? 'active' : '' ?>"
                   href="<?= Url::to(['filme/index', 'filter' => 'brevemente', 'q' => $q]) ?>">Brevemente</a>

            </div>


            <!-- Dropdown de cinemas -->
            <div class="dropdown-center <?= ($filter === 'brevemente' ? 'd-block d-sm-none' : 'd-block') ?>">
                <button class="btn btn-sm dropdown-toggle fw-medium" type="button" id="dropdownCinema" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= $cinemaSelecionado->nome ?? 'Brevemente' ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownCinema">
                    <li class="d-block d-sm-none">
                        <a class="dropdown-item <?= $filter === 'brevemente' ? 'active' : '' ?>"
                           href="<?= Url::to(['filme/index', 'filter' => 'brevemente', 'q' => $q]) ?>">Brevemente</a>
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

    <!-- Filmes -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">

        <?php if ($filmes): ?>
            <?php foreach ($filmes as $filme): ?>
                <div class="col">
                    <div class="h-100 border-0">
                        <?= $this->render('_card', [
                            'filme' => $filme,
                            'cinema_id' => $cinema_id
                        ]) ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="d-flex flex-column justify-content-center align-items-center w-100" style="height: 50vh;">
                <h4 class="text-muted text-center fw-semibold m-0">Nenhum filme encontrado!</h4>
                <a href="<?= Url::to(['filme/index', 'cinema_id' => $cinema_id, 'filter' => $filter]) ?>" class="text-muted mt-1 fs-14">Limpar pesquisa</a>
            </div>
        <?php endif; ?>

    </div>
</div>
