<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Alugueres';

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0">Os meus alugueres</h4>
        <?= Breadcrumbs::widget([
            'links' => [
                ['label' => 'Perfil', 'url' => ['perfil/index']],
                ['label' => 'Alugueres'],
            ],
            'homeLink' => false,
            'options' => ['class' => 'breadcrumb'],
        ]) ?>
    </div>

    <div class="box-gray shadow-sm w-100">
        <div class="d-flex flex-column gap-2">
            <?php if ($alugueres): ?>
                <?php foreach($alugueres as $aluguer): ?>
                    <div class="box-white rounded-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="mb-0 fw-semibold"><?= $aluguer->cinema->nome  . ' • ' . $aluguer->sala->nome ?></p>
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
                                    <p class="mb-0 fs-14 fw-semibold">Data</p>
                                    <span class="text-muted"><?= $aluguer->dataFormatada ?></span>
                                </div>
                                <div>
                                    <p class="mb-0 fs-14 fw-semibold">Horário</p>
                                    <span class="text-muted"><?= $aluguer->horaInicioFormatada . ' - ' . $aluguer->horaFimFormatada ?></span>
                                </div>
                            </div>
                            <a href="<?= Url::to(['aluguer-sala/view', 'id' => $aluguer->id]) ?>" class="btn btn-dark px-3 rounded-3">Detalhes</a>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="box-white rounded-4">
                    <div class="d-flex justify-content-center align-items-center w-100" style="height: 50vh;">
                        <h5 class="text-muted text-center fw-semibold m-0">Nenhum pedido encontrado!</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

