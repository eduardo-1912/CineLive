<?php

use common\components\Formatter;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var common\models\Compra $compra */
?>

<div class="box-white rounded-4">
    <div class="d-flex gap-0 gap-md-3 w-100">

        <?= Html::img($compra->sessao->filme->posterUrl, [
            'class' => 'd-none d-md-block img-fluid rounded-3 shadow-sm object-fit-cover',
            'style' => 'aspect-ratio: 2/3; height: 112px; object-fit: cover;',
            'alt' => $compra->sessao->filme->titulo,
        ]) ?>

        <div class="w-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <p class="mb-0 fw-semibold">
                        <?= "{$compra->sessao->filme->titulo} • {$compra->displayEstado()}" ?>
                    </p>
                    <span class="fs-14 text-muted"><?= $compra->sessao->cinema->nome ?></span>
                </div>
                <div class="text-end">
                    <p class="mb-0 fw-semibold"><?= Formatter::data($compra->data) ?></p>
                    <span class="fs-14 text-muted"><?= Formatter::preco($compra->total) ?></span>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <div class="w-100 d-flex gap-5">
                    <div>
                        <p class="mb-0 fs-14 fw-semibold"><?= $compra->getAttributeLabel('sessao') ?></p>
                        <span class="text-muted">
                            <?= Formatter::data($compra->sessao->data) . ' - ' . Formatter::hora($compra->sessao->hora_inicio) ?>
                        </span>
                    </div>
                    <div class="d-none d-sm-block">
                        <p class="mb-0 fs-14 fw-semibold"><?= $compra->getAttributeLabel('lugares') ?></p>
                        <span class="text-muted"><?= $compra->lugares ?></span>
                    </div>
                </div>

                <a href="<?= Url::to(['compra/view', 'id' => $compra->id]) ?>" class="btn btn-dark px-3 rounded-3">
                    Detalhes
                </a>
            </div>
        </div>

    </div>
</div>
