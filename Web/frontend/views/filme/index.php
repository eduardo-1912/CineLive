<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Filme[] $filmes */

$this->title = 'Filmes';
?>

<div class="container my-5">
    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($filmes as $filme): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <?= Html::img($filme->getPosterUrl(), [
                        'class' => 'card-img-top',
                        'alt' => $filme->titulo,
                        'style' => 'object-fit:cover; height:400px;'
                    ]) ?>

                    <div class="card-body">
                        <h5 class="card-title"><?= Html::encode($filme->titulo) ?></h5>
                        <p class="card-text text-muted small">
                            <?= Html::encode(mb_substr($filme->sinopse, 0, 100)) ?>...
                        </p>
                        <?= Html::a('Ver detalhes →', ['view', 'id' => $filme->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
