<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Comprar Bilhetes';

$sala = $sessao->sala;
$lugaresOcupados = $sessao->lugaresOcupados ?? [];

?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0"><?= Html::encode($this->title) ?></h4>
    </div>

    <div class="d-flex flex-column flex-lg-row gap-3">

        <!-- LEFT - CARD DO FILME COM DETALHES DA SESSÃO -->
        <div class="box-white shadow-sm">
            <div class="d-flex gap-0 gap-lg-3 flex-column flex-md-row flex-lg-column align-items-start h-100">

                <!-- POSTER -->
                <div>
                    <?= Html::img($sessao->filme->getPosterUrl(), [
                        'class' => 'd-none d-lg-block img-fluid rounded-4 shadow-sm object-fit-cover',
                        'style' => 'max-height: 70vh',
                        'alt' => $sessao->filme->titulo,
                    ]) ?>
                </div>

                <!-- DETALHES DO FILME E SESSÃO -->
                <div class="w-100 h-100 d-flex flex-column justify-content-between">
                    <div class="w-100 mb-2">

                        <!-- TÍTULO/RATING E DURAÇÃO -->
                        <div class="mb-4">
                            <h2 class="fw-bold mb-0"><?= $sessao->filme->titulo ?></h2>
                            <span class="text-muted small">
                                <?= $sessao->filme->rating ?>
                                •
                                <?= $sessao->filme->duracaoEmHoras ?>
                            </span>
                        </div>

                        <!-- DETALHES DA SESSÃO -->
                        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-2 w-100 gy-3 mb-3">
                            <div class="d-flex flex-column text-start">
                                <span class="fw-medium fs-14">Cinema</span>
                                <span class="text-muted"><?= $sessao->cinema->nome ?></span>
                            </div>
                            <div class="d-flex flex-column text-start">
                                <span class="fw-medium fs-14">Data</span>
                                <span class="text-muted"><?= $sessao->dataFormatada ?></span>
                            </div>
                            <div class="d-flex flex-column text-start">
                                <span class="fw-medium fs-14">Hora Início</span>
                                <span class="text-muted"><?= $sessao->horaInicioFormatada ?></span>
                            </div>
                            <div class="d-flex flex-column text-start">
                                <span class="fw-medium fs-14">Hora Fim</span>
                                <span class="text-muted"><?= $sessao->horaFimFormatada ?></span>
                            </div>
                        </div>

                    </div>

                    <!-- VOLTAR PARA VIEW DO FILME -->
                    <a href="<?= Url::to(['filme/view', 'id' => $sessao->filme->id, 'cinema_id' => $sessao->cinema->id, 'data' => $sessao->data, 'hora' => $sessao->horaInicioFormatada]) ?>"
                       class="btn btn-dark py-2 rounded-3 fs-14 w-100">Voltar ao filme</a>

                </div>
            </div>
        </div>

        <!-- RIGHT - MAPA COM SELEÇÃO DE LUGARES -->
        <div class="d-flex flex-column justify-content-between align-content-between box-gray text-center w-100 shadow-sm overflow-hidden">

            <!-- BADGES -->
            <div class="d-flex gap-2 mb-3 justify-content-center">
                <?php $badgeClasses = 'badge border shadow-sm rounded-pill px-2' ?>
                <h6><span class="<?= $badgeClasses ?> bg-light text-black">Disponível</span></h6>
                <h6><span class="<?= $badgeClasses ?> bg-danger">Selecionado</span></h6>
                <h6><span class="<?= $badgeClasses ?>" style="background-color: #9ca2a7;">Ocupado</span></h6>
            </div>

            <div class="text-center">

                <!-- ECRÃ -->
                <div class="d-flex flex-column align-items-center justify-content-center mb-4">
                    <p class="fw-semibold mb-1">Ecrã</p>
                    <div style="height: 4px; max-width: 32rem; background-color: #212529;" class="rounded-pill w-100"></div>
                </div>

                <!-- LUGARES -->
                <div class="d-inline-block" style="display: block; overflow-x: auto; white-space: nowrap; max-width: 100%;">
                    <div class="d-inline-block text-center">
                        <?php for ($fila = 1; $fila <= $sala->num_filas; $fila++): ?>
                            <div class="d-flex justify-content-center align-items-center mb-2 flex-nowrap">

                                <div class="fw-bold me-2" style="min-width: 20px;">
                                    <?= chr(64 + $fila) ?>
                                </div>

                                <?php foreach ($mapa[$fila] as $info): ?>

                                    <?php
                                    $classes = 'd-flex align-items-center rounded-3 shadow-sm justify-content-center btn fw-semibold mx-1 border ';

                                    if ($info['ocupado']) {
                                        $classes .= 'btn-secondary disabled pe-none';
                                    }
                                    elseif ($info['selecionado']) {
                                        $classes .= 'btn-danger';
                                    }
                                    else {
                                        $classes .= 'btn-light';
                                    }
                                    ?>

                                    <?= Html::a($info['label'], $info['url'], [
                                        'class' => $classes,
                                        'style' => 'min-width:45px; height:45px;',
                                    ]) ?>

                                <?php endforeach; ?>

                            </div>
                        <?php endfor; ?>

                    </div>
                </div>
            </div>

            <div class="mt-4 box-white w-100 shadow-sm rounded-4 d-flex align-items-stretch align-items-sm-center justify-content-between flex-column gap-2 flex-sm-row">

                <!-- TOTAL E LUGARES -->
                <div class="d-flex justify-content-between justify-content-sm-start gap-5">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14">Lugares</span>
                        <span class="text-muted fw-medium"><?= $lugaresImploded ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14">Total</span>
                        <span class="text-muted fw-medium"><?= $total ?></span>
                    </div>
                </div>

                <!-- BOTÃO DE PROCEDER A PAGAMENTO -->
                <button class="btn btn-danger rounded-3 fw-medium <?= (empty($lugaresSelecionados) ? 'disabled' : '') ?>" data-bs-toggle="modal" data-bs-target="#modalPagamento">
                    <?= !empty($lugaresSelecionados) ? 'Proceder ao pagamento' : 'Selecione lugares' ?>
                </button>

            </div>

        </div>

    </div>

</div>

<!-- MODAL PAGAMENTO -->
<div class="modal fade" id="modalPagamento" tabindex="-1" aria-labelledby="modalPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-0">
            <div class="modal-header border-0 pb-0">
                <h1 class="modal-title fs-5 fw-bold" id="modalPagamentoLabel">Escolha o método de pagamento</h1>
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="accordion" id="accordionMetodoPagamento">

                    <!-- MB WAY -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMbway" aria-expanded="false" aria-controls="collapseMbway">
                                MB WAY
                            </button>
                        </h2>
                        <div id="collapseMbway" class="accordion-collapse collapse" data-bs-parent="#accordionMetodoPagamento">
                            <div class="accordion-body">
                                <div class="mb-3 text-start">
                                    <label for="inputMbway" class="form-label fw-medium">Número de telemóvel</label>
                                    <input type="tel" class="form-control rounded-3" id="inputMbway" placeholder="912 345 678" maxlength="9">
                                </div>

                                <?php $form = ActiveForm::begin(['action' => ['compra/pay'], 'method' => 'post',]); ?>

                                    <?= Html::hiddenInput('sessao_id', $sessao->id) ?>
                                    <?= Html::hiddenInput('lugares', implode(',', $lugaresSelecionados)) ?>
                                    <?= Html::hiddenInput('metodo', 'mbway') ?>

                                    <button type="submit" class="btn btn-danger w-100 fw-medium rounded-3">Pagar <?= $total ?></button>

                                <?php ActiveForm::end(); ?>

                            </div>
                        </div>
                    </div>

                    <!-- CARTÃO -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCartao" aria-expanded="false" aria-controls="collapseCartao">
                                Cartão de Crédito / Débito
                            </button>
                        </h2>
                        <div id="collapseCartao" class="accordion-collapse collapse" data-bs-parent="#accordionMetodoPagamento">
                            <div class="accordion-body">
                                <div class="text-start">
                                    <div class="mb-3">
                                        <label for="inputCartao" class="form-label fw-medium">Número do Cartão</label>
                                        <input type="text" class="form-control rounded-3" id="inputCartao" placeholder="0000 0000 0000 0000" maxlength="19">
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="inputValidade" class="form-label fw-medium">Validade</label>
                                            <input type="text" class="form-control rounded-3" id="inputValidade" placeholder="MM/AA" maxlength="5">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="inputCvv" class="form-label fw-medium">CVV</label>
                                            <input type="text" class="form-control rounded-3" id="inputCvv" placeholder="123" maxlength="3">
                                        </div>
                                    </div>

                                    <?php $form = ActiveForm::begin(['action' => ['compra/pay'], 'method' => 'post',]); ?>

                                    <?= Html::hiddenInput('sessao_id', $sessao->id) ?>
                                    <?= Html::hiddenInput('lugares', implode(',', $lugaresSelecionados)) ?>
                                    <?= Html::hiddenInput('metodo', 'cartao') ?>

                                    <button type="submit" class="btn btn-danger w-100 fw-medium rounded-3">Pagar <?= $total ?></button>

                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MULTIBANCO -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMultibanco" aria-expanded="false" aria-controls="collapseMultibanco">
                                Referência Multibanco
                            </button>
                        </h2>
                        <div id="collapseMultibanco" class="accordion-collapse collapse" data-bs-parent="#accordionMetodoPagamento">
                            <div class="accordion-body text-center">
                                <p class="text-muted mb-3">Será gerada uma referência de pagamento.</p>

                                <?php $form = ActiveForm::begin(['action' => ['compra/pay'], 'method' => 'post',]); ?>

                                <?= Html::hiddenInput('sessao_id', $sessao->id) ?>
                                <?= Html::hiddenInput('lugares', implode(',', $lugaresSelecionados)) ?>
                                <?= Html::hiddenInput('metodo', 'multibanco') ?>

                                <button type="submit" class="btn btn-danger w-100 fw-medium rounded-3">Pagar <?= $total ?></button>

                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                    </div>

                </div> <!-- end accordion -->
            </div>
        </div>
    </div>
</div>
