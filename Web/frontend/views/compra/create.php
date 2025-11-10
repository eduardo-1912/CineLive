<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Comprar Bilhetes';
?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0"><?= Html::encode($this->title) ?></h4>
    </div>

    <div class="outer-box">
        <div class="inner-box">
            <div class="d-flex gap-2 align-items-start w-100">
                <div>
                    <?= Html::img($sessao->filme->getPosterUrl(), [
                        'class' => 'img-fluid rounded-3 shadow-sm',
                        'style' => 'aspect-ratio: 2/3; width: 8vh;',
                        'alt' => $sessao->filme->titulo,
                    ]) ?>
                </div>

                <div class="d-flex flex-column w-100">
                    <h4 class="fw-bold m-0"><?= $sessao->filme->titulo ?></h4>

                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4">
                        <div>
                            <span class="fw-semibold fs-14">Cinema</span>
                            <p class="text-muted mb-0"><?= $sessao->cinema->nome ?></p>
                        </div>
                        <div>
                            <span class="fw-semibold fs-14">Data</span>
                            <p class="text-muted mb-0"><?= $sessao->dataFormatada ?></p>
                        </div>
                        <div>
                            <span class="fw-semibold fs-14">Hora</span>
                            <p class="text-muted mb-0"><?= $sessao->horaInicioFormatada ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
