<?php

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
            <div class="d-flex gap-0 gap-lg-3 flex-column flex-md-row flex-lg-column align-items-start">

                <!-- POSTER -->
                <div>
                    <?= Html::img($sessao->filme->getPosterUrl(), [
                        'class' => 'd-none d-lg-block img-fluid rounded-4 shadow-sm object-fit-cover',
                        'style' => 'aspect-ratio: 2/3; max-height: 75vh',
                        'alt' => $sessao->filme->titulo,
                    ]) ?>
                </div>

                <!-- DETALHES DO FILME E SESSÃO -->
                <div class="w-100">
                    <div class="w-100 mb-4">
                        <h2 class="fw-bold mb-0"><?= Html::encode($sessao->filme->titulo) ?></h2>
                        <span class="text-muted small">
                            <?= Html::encode($sessao->filme->rating) ?>
                            •
                            <?= Html::encode($sessao->filme->duracaoEmHoras) ?>
                        </span>
                    </div>

                    <!-- Detalhes da sessão -->
                    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-2 w-100 gy-3">
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-15">Cinema</span>
                            <span class="text-muted"><?= $sessao->cinema->nome ?></span>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-15">Data</span>
                            <span class="text-muted"><?= $sessao->dataFormatada ?></span>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-15">Hora Início</span>
                            <span class="text-muted"><?= $sessao->horaInicioFormatada ?></span>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-15">Hora Fim</span>
                            <span class="text-muted"><?= $sessao->horaFimFormatada ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT - MAPA COM SELEÇÃO DE LUGARES -->
        <div class="d-inline-block box-gray text-center w-100 shadow-sm overflow-hidden">

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
                    <div style="height: 5px; max-width: 32rem; background-color: #212529;" class="rounded-pill w-100"></div>
                </div>

                <!-- LUGARES -->
                <div class="rounded-4 d-inline-block" style="display: block; overflow-x: auto; white-space: nowrap; max-width: 100%;">
                    <div class="d-inline-block text-center">
                        <?php for ($fila = 1; $fila <= $sala->num_filas; $fila++): ?>
                            <div class="d-flex justify-content-center align-items-center mb-2 flex-nowrap">

                                <!-- LETRA DA FILA -->
                                <div class="fw-bold me-2" style="min-width: 20px;">
                                    <?= chr(64 + $fila) ?>
                                </div>

                                <!-- LUGARES -->
                                <?php for ($coluna = 1; $coluna <= $sala->num_colunas; $coluna++): ?>
                                    <?php

                                    // CRIAR O LUGAR
                                    $lugar = chr(64 + $fila) . $coluna;

                                    // VER SE ESTÁ OCUPADO OU SELECIONADO
                                    $ocupado = in_array($lugar, $lugaresOcupados);
                                    $selecionado = in_array($lugar, $lugaresSelecionados);

                                    // CRIAR CÓPIA DO ARRAY DE LUGARES SELECIONADOS
                                    $novaSelecao = $lugaresSelecionados;

                                    // SE CLICOU NUM LUGAR QUE JÁ TINHA SELECIONADO --> DESMARCAR O LUGAR
                                    if ($selecionado) {
                                        $novaSelecao = array_diff($novaSelecao, [$lugar]);
                                    }

                                    // CASO TENHA SELECIONADO OU LUGAR NOVO --> ADICIONAR À LISTA
                                    else {
                                        $novaSelecao[] = $lugar;
                                    }

                                    // CRIAR URL PARA QUANDO ESCOLHE LUGAR NOVO
                                    $url = Url::to(['compra/create', 'sessao_id' => $sessao->id, 'lugares' => implode(',', $novaSelecao)]);

                                    $classes = 'd-flex align-items-center rounded-3 shadow-sm justify-content-center btn fw-semibold mx-1 border ';
                                    if ($ocupado) { $classes .= 'btn-secondary disabled pe-none'; }
                                    elseif ($selecionado) { $classes .= 'btn-danger'; }
                                    else { $classes .= 'btn-light'; }

                                    ?>

                                    <!-- CRIAR BOTÃO COM LUGAR -->
                                    <?= Html::a($coluna, $url, [
                                        'class' => $classes,
                                        'style' => 'min-width:45px; height:45px;',
                                        'title' => 'Clique para selecionar',
                                    ]) ?>

                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>


            <div class="mt-3 box-white w-100 shadow-sm rounded-4 d-flex align-items-stretch align-items-sm-center justify-content-between flex-column gap-2 flex-sm-row">
                <!-- TOTAL E LUGARES -->
                <div class="d-flex justify-content-between justify-content-sm-start gap-5">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-15">Lugares</span>
                        <span class="text-muted fw-medium"><?= (!empty($lugaresSelecionados) ? implode(', ', $lugaresSelecionados) : '-') ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-15">Total</span>
                        <span class="text-muted fw-medium"><?= $total > 0 ? number_format($total, 2) . '€' : '-' ?></span>
                    </div>
                </div>

                <!-- BOTÃO DE PROCEDER A PAGAMENTO -->
                <?= Html::a(
                    !empty($lugaresSelecionados) ? 'Proceder ao pagamento' : 'Selecione lugares',
                    ['compra/confirm', 'sessao_id' => $sessao->id, 'lugares' => implode(',', $lugaresSelecionados)],
                    ['class' => 'btn btn-danger rounded-3 fw-medium ' . (!empty($lugaresSelecionados) ? '' : 'disabled')]
                ) ?>
            </div>

        </div>

    </div>
</div>
