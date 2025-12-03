<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme[] $filmesEmExibicao */

?>

<div class="row">
    <div>
        <div class="card card-info shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-film me-1"></i> Filmes em Exibição
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($filmesEmExibicao as $filme): ?>
                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">

                            <a href="<?= Url::to(['filme/view', 'id' => $filme->id]) ?>">
                                <?= $filme->titulo ?>
                                <small class="text-muted"> (<?= $filme->duracao ?>min)</small>
                            </a>
                            <span class="badge bg-secondary"><?= $filme->rating ?></span>

                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

