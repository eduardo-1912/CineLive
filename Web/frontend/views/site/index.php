<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme[] $filmesCarousel */
/** @var int $cinema_id */
/** @var array $cinemaOptions */
/** @var int $filme_id */
/** @var array $filmeOptions */
/** @var string $data */
/** @var array $dataOptions */
/** @var int $sessao_id */
/** @var array $horaOptions */
/** @var common\models\Filme[] $filmesMaisVistos */
/** @var common\models\Filme $brevemente */

$this->title = 'Home';

?>
<div class="site-index">

    <?= $this->render('_carousel', ['filmes' => $filmesCarousel]) ?>

    <div class="container py-0">

        <section class="my-2 py-5">
            <div class="mb-4">
                <h4 class="page-title m-0">Comprar Bilhetes</h4>
            </div>

            <!-- Form para escolher a sessão -->
            <form method="get" action="<?= Url::to(['index']) ?>" class="d-flex flex-column flex-lg-row gap-2">

                <!-- Cinema -->
                <?= Html::dropDownList('cinema_id', $cinema_id, $cinemaOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Cinema',
                    'onchange' => 'this.form.submit()',
                ]) ?>

                <!-- Filme -->
                <?= Html::dropDownList('filme_id', $filme_id, $filmeOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Filme',
                    'onchange' => 'this.form.submit()',
                ]) ?>


                <!-- Data -->
                <?= Html::dropDownList('data', $data, $dataOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Data',
                    'onchange' => 'this.form.submit()',
                ]) ?>

                <!-- Hora -->
                <?= Html::dropDownList('sessao_id', $sessao_id, $horaOptions, [
                    'class' => 'form-select',
                    'prompt' => 'Hora',
                    'onchange' => 'this.form.submit()',
                ]) ?>

                <a href="<?= Url::to(['compra/create', 'sessao_id' => $sessao_id ?? null]) ?>"
                   class="btn btn-dark py-2 rounded-3 fs-14 w-100 <?= !$sessao_id ? 'disabled' : '' ?>">
                    Comprar Bilhetes
                </a>

            </form>

        </section>

        <section class="my-2 py-4">
            <div class="mb-4">
                <h4 class="page-title m-0">Filmes Mais Vistos</h4>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                <?php if ($filmesMaisVistos): ?>
                    <?php foreach ($filmesMaisVistos as $filme): ?>
                        <?= $this->render('@frontend/views/filme/_card', ['filme' => $filme]) ?>
                    <?php endforeach ?>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-center">
                <a href="<?= Url::to(['filme/index']) ?>"
                   class="btn btn-dark rounded-3 mt-4">
                    Ver Todos
                </a>
            </div>
        </section>

        <section class="d-none d-md-block my-2 py-4">
            <div class="mb-4">
                <h4 class="page-title m-0">Brevemente</h4>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                <?php if ($brevemente): ?>
                    <?php foreach ($brevemente as $filme): ?>
                        <?= $this->render('@frontend/views/filme/_card', ['filme' => $filme]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>
</div>
