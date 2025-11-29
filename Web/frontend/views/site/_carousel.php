<?php

/** @var yii\web\View $this */
/** @var common\models\Filme $carouselFilmes */

use yii\helpers\Url;

?>

<section id="filmeCarousel" class="carousel slide bg-black" data-bs-ride="carousel">

    <div class="carousel-indicators">
        <?php foreach ($carouselFilmes as $i => $filme): ?>
            <button type="button" data-bs-target="#filmeCarousel"
                    data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>">
            </button>
        <?php endforeach; ?>
    </div>

    <div class="carousel-inner">
        <?php foreach ($carouselFilmes as $i => $filme): ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <div class="container py-5">
                    <div class="d-flex flex-column flex-lg-row text-start align-items-center" style="gap: 5rem;">

                        <!-- Poster -->
                        <div class="col-lg-4 p-0">
                            <img class="img-fluid rounded-4 shadow-lg" style="aspect-ratio: 2/3"
                                 src="<?= $filme->getPosterUrl() ?>"
                                 alt="<?= $filme->titulo ?>">
                        </div>

                        <!-- Detalhes do Filme -->
                        <div class="col-lg p-0">

                            <p class="text-50 fs-14 mb-1 fw-medium">
                                <?= "{$filme->rating} • {$filme->idioma} • {$filme->duracaoEmHoras}" ?>
                            </p>

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
        <?php endforeach; ?>
    </div>

</section>