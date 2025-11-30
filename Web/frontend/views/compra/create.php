<?php

use common\components\Formatter;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Sessao $sessao */
/** @var array $lugaresSelecionados */
/** @var array $mapaLugares */
/** @var string $lugares */
/** @var string $total */

$this->title = 'Comprar Bilhetes';

?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0"><?= $this->title ?></h4>
    </div>

    <div class="d-flex flex-column flex-lg-row gap-3">

        <!-- Card dados do filme e sessão -->
        <div class="box-white shadow-sm">
            <div class="d-flex gap-0 gap-lg-3 flex-column flex-md-row flex-lg-column align-items-start h-100">

                <div>
                    <?= Html::img($sessao->filme->posterUrl, [
                        'class' => 'd-none d-lg-block img-fluid rounded-4 shadow-sm object-fit-cover',
                        'style' => 'max-height: 70vh',
                        'alt' => $sessao->filme->titulo,
                    ]) ?>
                </div>

                <div class="w-100 h-100 d-flex flex-column justify-content-between">
                    <div class="w-100 mb-2">

                        <div class="mb-4">
                            <h2 class="fw-bold mb-0"><?= $sessao->filme->titulo ?></h2>
                            <span class="text-muted small">
                                <?= $sessao->filme->rating . ' • ' . Formatter::horas($sessao->filme->duracao) ?>
                            </span>
                        </div>

                        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-2 w-100 gy-3 mb-3">
                            <div class="d-flex flex-column">
                                <span class="fw-semibold fs-14"><?= $sessao->getAttributeLabel('cinema') ?></span>
                                <span class="text-muted"><?= $sessao->cinema->nome ?></span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold fs-14"><?= $sessao->getAttributeLabel('sala') ?></span>
                                <span class="text-muted"><?= $sessao->sala->nome ?></span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold fs-14"><?= $sessao->getAttributeLabel('data') ?></span>
                                <span class="text-muted"><?= Formatter::data($sessao->data) ?></span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold fs-14"><?= $sessao->getAttributeLabel('horario') ?></span>
                                <span class="text-muted"><?= $sessao->horario ?></span>
                            </div>
                        </div>

                    </div>

                    <!-- Botão Voltar -->
                    <a href="<?= Url::to(['filme/view',
                        'id' => $sessao->filme->id, 'cinema_id' => $sessao->cinema_id,
                        'data' => Formatter::data($sessao->data), 'sessao_id' => $sessao->id]) ?>"
                        class="btn btn-dark py-2 rounded-3 fs-14 w-100">Voltar ao filme
                    </a>
                </div>
            </div>
        </div>

        <!-- Mapa de lugares -->
        <div class="d-flex flex-column justify-content-between align-content-between box-gray text-center w-100 shadow-sm overflow-hidden">

            <!-- Badges -->
            <div class="d-flex gap-2 mb-3 justify-content-center">
                <?php $badgeClasses = 'badge border shadow-sm rounded-pill px-2' ?>
                <h6><span class="<?= $badgeClasses ?> bg-light text-black">Disponível</span></h6>
                <h6><span class="<?= $badgeClasses ?> bg-danger">Selecionado</span></h6>
                <h6><span class="<?= $badgeClasses ?>" style="background-color: #9ca2a7;">Ocupado</span></h6>
            </div>

            <div class="text-center">

                <!-- Ecrã -->
                <div class="d-flex flex-column align-items-center justify-content-center mb-4">
                    <p class="fw-semibold mb-1">Ecrã</p>
                    <div style="height: 4px; max-width: 32rem; background-color: var(--gray-900);" class="rounded-pill w-100"></div>
                </div>

                <!-- Lugares -->
                <div class="d-inline-block" style="display: block; overflow-x: auto; white-space: nowrap; max-width: 100%;">
                    <div class="d-inline-block text-center">
                        <?php for ($fila = 1; $fila <= $sessao->sala->num_filas; $fila++): ?>
                            <div class="d-flex justify-content-center align-items-center mb-2 flex-nowrap">

                                <div class="fw-bold me-2" style="min-width: 20px;">
                                    <?= chr(64 + $fila) ?>
                                </div>

                                <?php foreach ($mapaLugares[$fila] as $lugar): ?>

                                    <?php
                                    $classes = 'lugar d-flex align-items-center rounded-3 shadow-sm justify-content-center btn fw-semibold mx-1 border ';

                                    if ($lugar['ocupado']) {
                                        $classes .= 'btn-secondary disabled pe-none';
                                    }
                                    elseif ($lugar['selecionado']) {
                                        $classes .= 'btn-danger';
                                    }
                                    else {
                                        $classes .= 'btn-light';
                                    }
                                    ?>

                                    <?= Html::a($lugar['label'], $lugar['url'], ['class' => $classes]) ?>

                                <?php endforeach; ?>

                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

            </div>

            <div class="mt-4 box-white w-100 shadow-sm rounded-4 d-flex align-items-stretch align-items-sm-center justify-content-between flex-column gap-2 flex-sm-row">

                <!-- Resumo -->
                <div class="d-flex justify-content-between justify-content-sm-start gap-5">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14">Lugares</span>
                        <span class="text-muted fw-medium"><?= $lugares ?></span>
                    </div>
                    <div class="d-flex flex-column text-end text-sm-start">
                        <span class="fw-medium fs-14">Total</span>
                        <span class="text-muted fw-medium"><?= $total ?></span>
                    </div>
                </div>

                <!-- Botão Pagar -->
                <button class="btn btn-danger rounded-3 fw-medium <?= (empty($lugaresSelecionados) ? 'disabled' : '') ?>"
                    data-bs-toggle="modal" data-bs-target="#modalPagamento">
                    <?= !empty($lugaresSelecionados) ? 'Proceder ao pagamento' : 'Selecione lugares' ?>
                </button>

            </div>

        </div>

    </div>

</div>

<!-- Modal Pagamento -->
<?= $this->render('_modalPagamento', [
    'sessao' => $sessao,
    'lugaresSelecionados' => $lugaresSelecionados,
    'total' => $total
]) ?>
