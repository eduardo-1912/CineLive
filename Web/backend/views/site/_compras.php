<?php

use common\helpers\Formatter;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var bool $verEstatisticas */
/** @var common\models\Compra[] $ultimasCompras */

?>
<div class="row">
    <div>
        <div class="card card-success shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-ticket-alt me-1"></i> Últimas Compras
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-responsive-md table-striped mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <?= $verEstatisticas ? '<th>Cinema</th>' : '' ?>
                        <th>Sessão</th>
                        <th>Filme</th>
                        <th>Estado</th>
                        <th class="text-end">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ultimasCompras as $compra): ?>
                        <tr>
                            <td><a href="<?= Url::to(['compra/view', 'id' => $compra->id]) ?>"><?= $compra->id ?></a></td>
                            <td><?= Formatter::data($compra->data) ?></td>
                            <?php if ($verEstatisticas): ?>
                                <td><a href="<?= Url::to(['/cinema/view', 'id' => $compra->sessao->cinema_id]) ?>"><?= $compra->sessao->cinema->nome ?></a></td>
                            <?php endif; ?>
                            <td><a href="<?= Url::to(['/sessao/view', 'id' => $compra->sessao->id]) ?>"><?= $compra->sessao->nome ?></a></td>
                            <td><a href="<?= Url::to(['filme/view', 'id' => $compra->sessao->filme->id]) ?>"><?= $compra->sessao->filme->titulo ?></a></td>
                            <td><?= ucfirst($compra->estado) ?></td>
                            <td class="text-end"><?= Formatter::preco($compra->total) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


