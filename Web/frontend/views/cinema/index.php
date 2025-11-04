<?php
use yii\helpers\Html;
use common\models\Sala;

/** @var yii\web\View $this */
/** @var common\models\Cinema[] $cinemas */

$this->title = 'Cinemas';
?>

<div class="container my-5">
    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <?php foreach ($cinemas as $cinema): ?>
        <!-- cartão do cinema: flex horizontal em MD+, coluna em XS/SM -->
        <div class="cinema-card bg-light rounded-3 overflow-hidden shadow-sm mb-4"
             style="display:flex; flex-direction:column; gap:0;">

            <!-- wrapper que força row em MD+ -->
            <div class="d-flex flex-column flex-md-row" style="width:100%;">

                <!-- mapa: ocupa metade no MD+ -->
                <div class="cinema-map" style="flex:0 0 50%; max-width:50%;">
                    <iframe
                            width="100%"
                            height="320"
                            style="border:0; display:block;"
                            loading="lazy"
                            allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=<?= Html::encode($cinema->latitude) ?>,<?= Html::encode($cinema->longitude) ?>&hl=pt&z=15&output=embed">
                    </iframe>
                </div>

                <!-- info: ocupa metade no MD+ -->
                <div class="cinema-info p-4 d-flex flex-column justify-content-center"
                     style="flex:1 1 50%; max-width:50%;">
                    <h4 class="fw-bold mb-1"><?= Html::encode($cinema->nome) ?></h4>
                    <p class="text-muted mb-3">Gerenciado por: <?= Html::encode($cinema->gerente->profile->nome ?? '—') ?></p>

                    <p class="mb-2"><strong>Morada:</strong><br>
                        <?= Html::encode($cinema->rua . ", " . $cinema->codigo_postal . ", " . $cinema->cidade) ?>
                    </p>

                    <p class="mb-2"><strong>Telemóvel:</strong><br>
                        <?= Html::encode($cinema->telefone) ?>
                    </p>

                    <p class="mb-2"><strong>Email:</strong><br>
                        <?= Html::encode($cinema->email) ?>
                    </p>

                    <p class="mb-2"><strong>Horário:</strong><br>
                        <?= Html::encode($cinema->horario_abertura . " - " . $cinema->horario_fecho) ?>
                    </p>

                    <p class="mb-0"><strong>Capacidade:</strong><br>
                        <?= Html::encode(Sala::find()->where(['cinema_id' => $cinema->id])->count() . " salas") ?>
                    </p>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>
