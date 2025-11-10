<?php

/** @var yii\web\View $this */

use common\models\User;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$isGerente = $currentUser->can('gerente') && !$isAdmin;
$isFuncionario = $currentUser->can('funcionario') && !$isGerente;
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

$this->title = 'Dashboard';
?>

<div class="site-index container-fluid d-flex flex-column gap-4">
    <div class="row row-cols-1 row-cols-sm-3 g-3 text-start">
        <div class="col">
            <div class="small-box bg-info p-0 mb-0">
                <div class="inner d-flex flex-column align-items-start">
                    <h3><?= $totalFilmesEmExibicao ?></h3>
                    <p>Filmes em exibição</p>
                </div>
                <div class="icon">
                    <i class="fas fa-film"></i>
                </div>
                <a href="<?= Url::to(['/filme/index']) ?>" class="small-box-footer">
                    Ver Filmes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col">
            <div class="small-box bg-danger p-0 mb-0">
                <div class="inner d-flex flex-column align-items-start">
                    <h3><?= $totalAlugueres ?></h3>
                    <p><?= ($isGerente || $isAdmin ? 'Pedidos de aluguer pendentes' : 'Alugueres agendados para hoje') ?></p>
                </div>
                <div class="icon">
                    <i class="fas fa-film"></i>
                </div>
                <a href="<?= Url::to(['/aluguer/index']) ?>" class="small-box-footer">
                    Ver Alugueres <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col">
            <div class="small-box bg-warning p-0 mb-0">
                <div class="inner d-flex flex-column align-items-start">
                    <h3><?= $totalSessoesHoje ?></h3>
                    <p>Sessões agendadas para hoje</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="<?= Url::to(['/sessao/index']) ?>" class="small-box-footer">
                    Ver Sessões <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>



    <!-- GRÁFICO DE VENDAS POR CINEMA PARA ADMIN -->
    <?php if ($isAdmin): ?>
        <div class="row mt-4">
            <div>
                <div class="card card-primary shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-1"></i> Vendas por Cinema
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="vendasChart" style="min-height: 300px; height: 350px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">

        <!-- ÚLTIMAS COMPRAS -->
        <div>
            <div class="card card-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ticket-alt me-1"></i> Últimas Compras
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <?= $isAdmin ? '<th>Cinema</th>' : '' ?>
                            <th>Sessão</th>
                            <th>Filme</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($ultimasCompras)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sem compras registadas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimasCompras as $compra): ?>
                                <tr>
                                    <td><a href="<?= Url::to(['compra/view', 'id' => $compra->id]) ?>"><?= $compra->id ?></a></td>
                                    <td><?= $compra->dataFormatada ?></td>
                                    <?php if ($isAdmin): ?>
                                        <td><a href="<?= Url::to(['/cinema/view', 'id' => $compra->sessao->cinema_id]) ?>"><?= $compra->sessao->cinema->nome ?></a></td>
                                    <?php endif; ?>
                                    <td><a href="<?= Url::to(['/sessao/view', 'id' => $compra->sessao->id]) ?>"><?= $compra->sessao->nome ?></a></td>
                                    <td><a href="<?= Url::to(['filme/view', 'id' => $compra->sessao->filme->id]) ?>"><?= $compra->sessao->filme->titulo ?></a></td>
                                    <td><?= ucfirst($compra->estado) ?></td>
                                    <td class="text-end"><?= $compra->totalFormatado ?>€</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- FILMES EX EXIBIÇÃO -->
        <div>
            <div class="card card-info shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-film me-1"></i> Filmes em Exibição
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($filmesEmExibicao)): ?>
                        <p class="text-muted mb-0">Nenhum filme em exibição.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($filmesEmExibicao as $filme): ?>
                                <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">

                                        <a href="<?= Url::to(['filme/view', 'id' => $filme->id]) ?>">
                                            <?= Html::encode($filme->titulo) ?>
                                            <small class="text-muted"> (<?= Html::encode($filme->duracao) ?>min)</small>
                                        </a>
                                        <span class="badge bg-secondary"><?= $filme->rating ?></span>

                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



</div>

<!-- BAR-CHART GENÉRICO -->
<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);
?>
<?php
$labels = Json::encode($labelsCinemas);
$data = Json::encode($valoresVendas);

$this->registerJs(<<<JS
if ($('#vendasChart').length) {
    const ctx = document.getElementById('vendasChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: $labels,
            datasets: [{
                label: '',
                data: $data,
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8',
                    '#6610f2',
                ],
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 0 }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const valor = context.parsed.y ?? context.parsed;
                            return context.dataset.label + ': ' + valor.toLocaleString('pt-PT', { minimumFractionDigits: 2 }) + ' €';
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Cinema'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total (€)'
                    }
                }
            }
        }
    });
}
JS);
?>

