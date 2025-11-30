<?php

/** @var yii\web\View $this */
/** @var common\models\Sessao $sessao */
/** @var array $lugaresSelecionados */
/** @var string $total */

?>

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

                                <?= $this->render('_formPagamento', [
                                    'sessao' => $sessao,
                                    'lugaresSelecionados' => $lugaresSelecionados,
                                    'total' => $total,
                                    'metodo' => 'mbway'
                                ]) ?>

                            </div>
                        </div>
                    </div>

                    <!-- Cartão -->
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

                                    <?= $this->render('_formPagamento', [
                                        'sessao' => $sessao,
                                        'lugaresSelecionados' => $lugaresSelecionados,
                                        'total' => $total,
                                        'metodo' => 'cartao'
                                    ]) ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Multibanco -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMultibanco" aria-expanded="false" aria-controls="collapseMultibanco">
                                Referência Multibanco
                            </button>
                        </h2>
                        <div id="collapseMultibanco" class="accordion-collapse collapse" data-bs-parent="#accordionMetodoPagamento">
                            <div class="accordion-body text-center">
                                <p class="text-muted mb-3">Será gerada uma referência de pagamento.</p>

                                <?= $this->render('_formPagamento', [
                                    'sessao' => $sessao,
                                    'lugaresSelecionados' => $lugaresSelecionados,
                                    'total' => $total,
                                    'metodo' => 'multibanco'
                                ]) ?>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>