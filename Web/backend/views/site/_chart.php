<?php

use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var array $labelsCinemas */
/** @var array $valoresVendas */

?>

<div class="d-none d-lg-flex row mt-4">
    <div>
        <div class="card card-primary shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-1"></i> Vendas por Cinema (<?= date('Y') ?>)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="vendasChart" style="min-height: 300px; height: 350px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

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

