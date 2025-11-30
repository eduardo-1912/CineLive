<?php

use common\components\Formatter;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */
/** @var int $cinema_id */
/** @var array $cinemaOptions */
/** @var string $data */
/** @var array $dataOptions */
/** @var int $sessao_id */
/** @var array $horaOptions */

$this->title = $model->titulo;
?>

<div class="container">
    <div class="row flex-column-reverse flex-md-row">

        <div class="col-md-4">

            <!-- Poster -->
            <?= Html::img($model->getPosterUrl(), [
                'class' => 'img-fluid d-none d-md-block rounded-4 shadow-sm',
                'style' => 'aspect-ratio: 2/3;',
                'alt' => $model->titulo,
            ]) ?>

            <!-- Sessões -->
            <div class="mt-4 mt-md-3">

                <!-- Form para escolher a sessão -->
                <form method="get" action="<?= Url::to(['filme/view']) ?>" class="d-flex flex-column gap-2">
                    <?= Html::hiddenInput('id', $model->id) ?>

                    <!-- Cinema -->
                    <?= Html::dropDownList('cinema_id', $cinema_id, $cinemaOptions, [
                        'class' => 'form-select',
                        'prompt' => 'Cinema',
                        'onchange' => 'this.form.submit()',
                        'disabled' => $model->isEstadoBrevemente(),
                    ]) ?>

                    <div class="d-flex w-100 gap-2">
                        <!-- Data -->
                        <?= Html::dropDownList('data', $data, $dataOptions, [
                            'class' => 'form-select',
                            'prompt' => 'Data',
                            'onchange' => 'this.form.submit()',
                            'disabled' => $model->isEstadoBrevemente(),
                        ]) ?>

                        <!-- Hora -->
                        <?= Html::dropDownList('sessao_id', $sessao_id, $horaOptions, [
                            'class' => 'form-select',
                            'prompt' => 'Hora',
                            'onchange' => 'this.form.submit()',
                            'disabled' => $model->isEstadoBrevemente(),
                        ]) ?>
                    </div>

                    <a href="<?= Url::to(['compra/create', 'sessao_id' => $sessao_id ?? null]) ?>"
                       class="btn btn-dark py-2 rounded-3 fs-14 w-100 <?= !$sessao_id ? 'disabled' : '' ?>">
                        <?= !$model->isEstadoBrevemente() ? 'Comprar Bilhetes' : 'Brevemente' ?>
                    </a>
                </form>

            </div>

        </div>

        <!-- Dados do filme -->
        <div class="col-md-8 mt-0">

            <div class="d-flex w-100 gap-2">
            <?= Html::img($model->posterUrl, [
                'class' => 'img-fluid d-block d-md-none rounded-3 shadow-sm',
                'style' => 'aspect-ratio: 2/3; max-height: 66px;',
                'alt' => $model->titulo,
            ]) ?>

            <!-- Título, rating e géneros -->
            <div class="d-flex flex-column gap-2 mb-3 w-100">

                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fw-bold m-0"><?= $model->titulo ?></h2>
                    <h5 class="m-0 py-1 px-3 rounded-pill fw-semibold text-white
                        <?= in_array($model->rating, $model::optsRatingKids()) ? 'bg-success' : 'bg-danger' ?>">
                        <?= $model->rating ?>
                    </h5>
                </div>

                <!-- Géneros -->
                <div class="d-flex mb-1 gap-1">
                    <?php if ($model->generos): ?>
                        <?php foreach ($model->generos as $genero): ?>
                            <span class="badge-genero"><?= $genero->nome ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

            <!-- Dados -->
            <div class="row row-cols-2 row-cols-sm-4">
                <div>
                    <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('estreia') ?></span>
                    <p class="text-muted"><?= Formatter::data($model->estreia) ?></p>
                </div>
                <div>
                    <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('duracao') ?></span>
                    <p class="text-muted"><?= Formatter::horas($model->duracao) ?></p>
                </div>
                <div>
                    <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('idioma') ?></span>
                    <p class="text-muted"><?= $model->idioma ?></p>
                </div>
                <div>
                    <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('realizacao') ?></span>
                    <p class="text-muted"><?= $model->realizacao ?></p>
                </div>
            </div>

            <!-- Sinopse -->
            <div class="mt-3">
                <h5><?= $model->getAttributeLabel('sinopse') ?></h5>
                <p class="text-muted" style="min-height: 3lh"><?= nl2br($model->sinopse) ?></p>
            </div>

            <!-- Trailer -->
            <?php if ($model->trailer_url): ?>
                <div class="mt-4">
                    <h5><?= $model->getAttributeLabel('trailer') ?></h5>
                    <div class="rounded-3 trailer-box overflow-hidden shadow-sm">
                        <iframe width="100%" class="trailer-box" allowfullscreen
                                src="<?= str_replace('watch?v=', 'embed/', $model->trailer_url) ?>">
                        </iframe>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
