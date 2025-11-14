<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$iconsPath = '@webroot/icons/';

$this->title = 'Home';
?>
<div class="site-index">
    <section id="filmeCarousel" class="carousel slide" data-bs-ride="carousel">

        <!-- INDICADORES -->
        <div class="carousel-indicators">
            <?php foreach ($carouselFilmes as $i => $filme): ?>
                <button type="button"
                        data-bs-target="#filmeCarousel"
                        data-bs-slide-to="<?= $i ?>"
                        class="<?= $i === 0 ? 'active' : '' ?>">
                </button>
            <?php endforeach; ?>
        </div>

        <div class="carousel-inner">
            <?php foreach ($carouselFilmes as $i => $filme): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="bg-black">
                        <div class="container py-5">

                            <div class="d-flex flex-column flex-lg-row text-start align-items-center" style="gap: 5rem;">

                                <!-- POSTER -->
                                <div class="col-lg-4 p-0">
                                    <img class="img-fluid rounded-4 shadow-lg" style="aspect-ratio: 2/3"
                                         src="<?= $filme->getPosterUrl() ?>"
                                         alt="<?= $filme->titulo ?>">
                                </div>

                                <!-- DETALHES -->
                                <div class="col-lg p-0">

                                    <p class="text-50 fs-14 mb-1 fw-medium"><?= $filme->rating ?> • <?= $filme->idioma ?> • <?= $filme->duracao ?>min</p>

                                    <h1 class="text-white fw-bold display-5 mb-3"><?= $filme->titulo ?></h1>

                                    <p class="text-50 mb-4" style="height: 3lh; overflow: hidden"><?= $filme->sinopse ?></p>

                                    <a href="<?= Url::to(['filme/view', 'id' => $filme->id]) ?>"
                                       class="btn btn-light fs-15 rounded-3 fw-medium">
                                        Comprar Bilhetes
                                    </a>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <div class="container py-0">
        <section class="my-2 py-5">
            <div class="mb-4">
                <h4 class="page-title m-0">Comprar Bilhetes</h4>
            </div>

            <!-- FORM PARA ESCOLHER A SESSÃO -->
            <form method="get" action="<?= Url::to(['index']) ?>" class="d-flex flex-column flex-lg-row gap-2">

                <!-- CINEMA -->
                <?= Html::dropDownList('cinema_id', $cinema_id, $listaCinemas, [
                    'class' => 'form-select',
                    'prompt' => 'Cinema',
                    'onchange' => 'this.form.submit()',
                ]) ?>

                <!-- FIME -->
                <?= Html::dropDownList('filme_id', $filme_id, $listaFilmes, [
                    'class' => 'form-select',
                    'prompt' => 'Filme',
                    'onchange' => 'this.form.submit()',
                ]) ?>


                <!-- DATA -->
                <?= Html::dropDownList('data', $dataSelecionada, $listaDatas, [
                    'class' => 'form-select',
                    'prompt' => 'Data',
                    'onchange' => 'this.form.submit()',
                ]) ?>

                <!-- HORA -->
                <?= Html::dropDownList('hora', $horaSelecionada, $listaHoras, [
                    'class' => 'form-select',
                    'prompt' => 'Hora',
                    'onchange' => 'this.form.submit()',
                ]) ?>

                <a href="<?= Url::to(['compra/create', 'sessao_id' => $sessaoSelecionada->id ?? null]) ?>"
                   class="btn btn-dark py-2 rounded-3 fs-14 w-100 <?= !$sessaoSelecionada ? 'disabled' : '' ?>">
                    Comprar Bilhetes
                </a>

            </form>

        </section>
        <section class="my-2 py-5">
            <div class="mb-4">
                <h4 class="page-title m-0">Filmes Mais Vistos</h4>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 gx-2">
                <?php foreach ($filmesMaisVistos as $filme): ?>
                    <a href="<?= Url::to(['filme/view', 'id' => $filme->id]) ?>" class="card-filme text-center text-decoration-none text-black">
                        <?= Html::img($filme->getPosterUrl(), [
                            'class' => 'img-fluid rounded-4 shadow-sm',
                            'style' => 'aspect-ratio: 2/3;',
                            'alt' => $filme->titulo,
                        ]) ?>
                        <h5 class="mb-4 mb-lg-0 mt-1 fw-semibold fs-6"><?= $filme->titulo ?></h5>
                    </a>
                <?php endforeach; ?>
            </div>

        </section>
        <section class="d-none d-md-block my-2 py-5">
            <div class="d-flex justify-content-between align-items-center mb-4" style="min-height: 35px;">
                <h4 class="page-title m-0">Novas Estreias</h4>
                <div class="d-flex gap-1 align-items-center">
                    <!-- DROPDOWN DE CINEMAS -->
                    <div class="dropdown-center">
                        <button class="btn btn-sm dropdown-toggle fw-medium" type="button" id="dropdownCinema" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= $currentCinema ?? 'Selecione o cinema' ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownCinema">
                            <?php foreach ($cinemas as $cinema): ?>
                                <li>
                                    <a class="dropdown-item <?= $cinema_id == $cinema->id ? 'active' : '' ?>"
                                       href="<?= Url::to(['index', 'cinema_id' => $cinema->id]) ?>">
                                        <?= $cinema->nome ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CARD DE FILMES -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                <?php if ($novasEstreias): ?>
                    <?php foreach ($novasEstreias as $filme): ?>
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

            <div class="d-flex justify-content-center">
                <a href="<?= Url::to(['filme/index', 'cinema_id' => $cinema_id]) ?>"
                   class="btn btn-dark rounded-3 mt-4">
                    Ver Todos os Filmes
                </a>
            </div>


        </section>

    </div>
</div>
