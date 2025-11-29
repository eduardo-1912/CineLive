<?php

use yii\helpers\Url;

/** @var common\models\AluguerSala $aluguer */

?>

<div class="box-white rounded-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <p class="mb-0 fw-semibold"><?= "{$aluguer->cinema->nome} • {$aluguer->sala->nome}" ?></p>
            <span class="fs-14 text-muted"><?= $aluguer->tipo_evento ?></span>
        </div>
        <div class="text-end">
            <p class="mb-0 fw-semibold"><?= $aluguer->estadoFormatado ?></p>
            <span class="fs-14 text-muted">Pedido #<?= $aluguer->id ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between">
        <div class="w-100 d-flex gap-2 gap-sm-5">
            <div>
                <p class="mb-0 fs-14 fw-semibold"><?= $aluguer->getAttributeLabel('data') ?></p>
                <span class="text-muted"><?= $aluguer->dataFormatada ?></span>
            </div>
            <div>
                <p class="mb-0 fs-14 fw-semibold"><?= $aluguer->getAttributeLabel('horario') ?></p>
                <span class="text-muted"><?= $aluguer->horario ?></span>
            </div>
        </div>
        <a href="<?= Url::to(['aluguer-sala/view', 'id' => $aluguer->id]) ?>" class="btn btn-dark px-3 rounded-3">
            Detalhes
        </a>
    </div>

</div>