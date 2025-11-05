<?php
/** @var common\models\Sessao $model */

use yii\helpers\Html;

$sala = $model->sala;
$lugaresOcupados = $model->lugaresOcupados;
$lugaresConfirmados = $model->lugaresConfirmados;
$mapaLugaresCompra = $model->mapaLugaresCompra; // ['A1' => 12, 'B2' => 7, ...]
?>

<div class="mb-4 text-center">
    <div class="mt-3 d-flex justify-content-center gap-3">
        <span class="badge bg-secondary">Livre</span>
        <span class="badge bg-warning text-dark">Ocupado</span>
        <span class="badge bg-danger">Confirmado</span>
    </div>

    <div class="d-inline-block bg-light p-4 rounded-3 shadow-sm">
        <?php for ($fila = 1; $fila <= $sala->num_filas; $fila++): ?>
            <div class="d-flex justify-content-center mb-2 flex-wrap">
                <?php for ($coluna = 1; $coluna <= $sala->num_colunas; $coluna++): ?>
                    <?php
                    $lugar = chr(64 + $fila) . $coluna;
                    $ocupado = in_array($lugar, $lugaresOcupados);
                    $confirmado = in_array($lugar, $lugaresConfirmados);
                    $compraId = $mapaLugaresCompra[$lugar] ?? null;

                    $classes = 'lugar border fw-bold text-center rounded mx-2 my-0 ';
                    if ($confirmado) {
                        $classes .= 'bg-danger';
                    } elseif ($ocupado) {
                        $classes .= 'bg-warning';
                    } else {
                        $classes .= 'bg-secondary';
                    }

                    $content = Html::tag('div', $lugar, [
                        'class' => $classes . ' d-flex align-items-center justify-content-center',
                        'title' => $compraId ? "Compra #{$compraId}" : 'Lugar livre',
                    ]);

                    echo $compraId
                        ? Html::a($content, ['compra/view', 'id' => $compraId], ['class' => 'text-decoration-none'])
                        : $content;
                    ?>
                <?php endfor; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<?php
$css = <<<CSS
.lugar {
    width: 40px;
    height: 40px;
    line-height: 50px;
    font-size: 0.9rem;
    transition: transform 0.1s ease-in-out;
}
CSS;
$this->registerCss($css);
?>
