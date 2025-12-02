<?php

/** @var yii\web\View $this */
/** @var int $totalFilmes */
/** @var int $totalAlugueres */
/** @var int $totalSessoes */
/** @var bool $verEstatisticas */
/** @var common\models\Compra[] $ultimasCompras */
/** @var common\models\Filme[] $filmesEmExibicao */
/** @var array $labelsCinemas */
/** @var array $valoresVendas */

$this->title = 'Dashboard';

?>

<div class="site-index container-fluid d-flex flex-column gap-4">

    <?= $this->render('_cards', [
        'totalFilmes' => $totalFilmes,
        'totalAlugueres' => $totalAlugueres,
        'totalSessoes' => $totalSessoes,
    ]) ?>

    <?php if ($verEstatisticas): ?>
        <?= $this->render('_chart', [
            'labelsCinemas' => $labelsCinemas,
            'valoresVendas' => $valoresVendas,
        ]) ?>
    <?php endif; ?>

    <?php if ($ultimasCompras): ?>
        <?= $this->render('_compras', [
            'verEstatisticas' => $verEstatisticas,
            'ultimasCompras' => $ultimasCompras,
        ]) ?>
    <?php endif; ?>

    <?php if ($filmesEmExibicao): ?>
        <?= $this->render('_filmes', ['filmesEmExibicao' => $filmesEmExibicao,]) ?>
    <?php endif; ?>

</div>

