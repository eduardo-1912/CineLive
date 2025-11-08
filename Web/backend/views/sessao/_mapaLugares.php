<?php
/** @var common\models\Sessao $model */

use yii\helpers\Html;

$sala = $model->sala;
$lugaresOcupados = $model->lugaresOcupados;
$lugaresConfirmados = $model->lugaresConfirmados;
$mapaLugaresCompra = $model->mapaLugaresCompra;
?>

<div class="text-center">
    <div class="mb-2 d-flex justify-content-center gap-3">
        <h5><span class="badge bg-secondary">Livre</span></h5>
        <h5><span class="badge bg-danger text-dark">Ocupado</span></h5>
        <h5><span class="badge bg-success">Confirmado</span></h5>
    </div>

    <div class="d-inline-block bg-light p-4 rounded-4 shadow-sm">
        <?php for ($fila = 1; $fila <= $sala->num_filas; $fila++): ?>
            <div class="d-flex justify-content-center mb-2 flex-wrap">
                <?php for ($coluna = 1; $coluna <= $sala->num_colunas; $coluna++): ?>
                    <?php

                    // CRIAR LUGAR (EX.: A5, B6, C7)
                    $lugar = chr(64 + $fila) . $coluna;
                    $ocupado = in_array($lugar, $lugaresOcupados);
                    $confirmado = in_array($lugar, $lugaresConfirmados);
                    $compraId = $mapaLugaresCompra[$lugar] ?? null;

                    $classes = 'lugar fw-semibold text-center rounded-3 mx-2 my-0 d-flex align-items-center justify-content-center ';
                    if ($confirmado) { $classes .= 'bg-success'; }
                    elseif ($ocupado) { $classes .= 'bg-danger'; }
                    else { $classes .= 'bg-secondary opacity-75'; }
                    ?>

                    <?php if ($compraId): ?>
                        <?= Html::a(Html::encode($lugar), ['compra/view', 'id' => $compraId],
                            ['class' => $classes . ' text-decoration-none', 'title' => "Compra #{$compraId}",]) ?>
                    <?php else: ?>
                        <div class="<?= $classes ?>" title="Lugar livre">
                            <?= Html::encode($lugar) ?>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
