<?php

use common\models\Filme;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */

$this->title = $model->titulo;
?>

<div class="container">
    <div class="row flex-column-reverse flex-md-row">

        <!-- LEFT - POSTER E SESSÕES -->
        <div class="col-md-4">

            <!-- POSTER -->
            <?= Html::img($model->getPosterUrl(), [
                'class' => 'img-fluid d-none d-md-block rounded-4 shadow-sm',
                'style' => 'aspect-ratio: 2/3;',
                'alt' => $model->titulo,
            ]) ?>

            <!-- SESSÕES -->
            <div class="mt-3">

                <!-- FORM PARA ESCOLHER A SESSÃO -->
                <form method="get" action="<?= Url::to(['filme/view']) ?>" class="d-flex flex-column gap-2">
                    <?= Html::hiddenInput('id', $model->id) ?>

                    <!-- CINEMA -->
                    <?= Html::dropDownList('cinema_id', $cinema_id, $listaCinemas, [
                        'class' => 'form-select',
                        'prompt' => 'Cinema',
                        'onchange' => 'this.form.submit()',
                        'disabled' => $model->isEstadoBrevemente(),
                    ]) ?>

                    <div class="d-flex w-100 gap-2">
                        <!-- DATA -->
                        <?= Html::dropDownList('data', $dataSelecionada, $listaDatas, [
                            'class' => 'form-select',
                            'prompt' => 'Data',
                            'onchange' => 'this.form.submit()',
                            'disabled' => $model->isEstadoBrevemente(),
                        ]) ?>

                        <!-- HORA -->
                        <?= Html::dropDownList('hora', $horaSelecionada, $listaHoras, [
                            'class' => 'form-select',
                            'prompt' => 'Hora',
                            'onchange' => 'this.form.submit()',
                            'disabled' => $model->isEstadoBrevemente(),
                        ]) ?>
                    </div>

                    <a href="<?= Url::to(['compra/create', 'sessao_id' => $sessaoSelecionada->id ?? null]) ?>"
                       class="btn btn-dark py-2 rounded-3 fs-14 w-100 <?= !$sessaoSelecionada ? 'disabled' : '' ?>">
                        <?= !$model->isEstadoBrevemente() ? 'Comprar Bilhetes' : 'Brevemente' ?>
                    </a>
                </form>

            </div>

        </div>

        <!-- RIGHT - DADOS DO FILME -->
        <div class="col-md-8 mt-0">

            <div class="d-flex w-100 gap-2">
            <?= Html::img($model->getPosterUrl(), [
                'class' => 'img-fluid d-block d-md-none rounded-3 shadow-sm',
                'style' => 'aspect-ratio: 2/3; max-height: 66px;',
                'alt' => $model->titulo,
            ]) ?>

            <!-- TÍTULO/RATING E GÉNEROS-->
            <div class="d-flex flex-column gap-2 mb-3 w-100">

                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fw-bold m-0"><?= $model->titulo ?></h2>
                    <h5 class="m-0 py-1 px-3 rounded-pill fw-semibold text-white
                        <?= $model->isRatingKids() ? 'bg-success' : 'bg-danger' ?>">
                        <?= $model->rating ?>
                    </h5>
                </div>
                <div class="d-flex mb-1 gap-1">
                    <?php if ($model->generos): ?>
                        <?php foreach ($model->generos as $genero): ?>
                            <span class="badge-genero"><?= $genero->nome ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            </div>

            <!-- OUTROS DADOS -->
            <div class="row row-cols-2 row-cols-sm-4">
                <div>
                    <span class="fw-semibold fs-14">Estreia</span>
                    <p class="text-muted"><?= $model->estreiaFormatada ?></p>
                </div>
                <div>
                    <span class="fw-semibold fs-14">Duração</span>
                    <p class="text-muted"><?= $model->duracaoEmHoras ?></p>
                </div>
                <div>
                    <span class="fw-semibold fs-14">Idioma</span>
                    <p class="text-muted"><?= $model->idioma ?></p>
                </div>
                <div>
                    <span class="fw-semibold fs-14">Realização</span>
                    <p class="text-muted"><?= $model->realizacao ?></p>
                </div>
            </div>

            <!-- SINOPSE -->
            <div class="mt-3">
                <h5>Sinopse</h5>
                <p class="text-muted" style="min-height: 3lh"><?= nl2br($model->sinopse) ?></p>
            </div>

            <!-- TRAILER -->
            <?php if ($model->trailer_url): ?>
                <div class="mt-4">
                    <h5>Trailer</h5>
                    <div class="rounded-3 overflow-hidden shadow-sm" style="height: 374px">
                        <iframe width="100%" height="374" allowfullscreen
                                src="<?= str_replace('watch?v=', 'embed/', $model->trailer_url) ?>">
                        </iframe>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
