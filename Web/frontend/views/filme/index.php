<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Filme[] $filmes */

$this->title = 'Filmes';
?>

<div class="container">
    <h3 class="page-title"><?= Html::encode($this->title) ?></h3>

    <div class="row row-cols-1 row-cols-md-4 g-3">
        <?php foreach ($filmes as $filme): ?>
            <div class="col">
                <div class="h-100 border-0">
                    <a href="<?= Url::to(['filme/view?id=']) . $filme->id ?>" class="card-filme text-center text-decoration-none text-black d-flex flex-column gap-1">
                        <?= Html::img($filme->getPosterUrl(), [
                            'class' => 'card-img-top shadow-sm rounded-4',
                            'alt' => $filme->titulo,
                            'style' => 'object-fit: cover; height: 400px;'
                        ]) ?>
                        <h5 class="fw-semibold fs-6"><?= Html::encode($filme->titulo) ?></h5>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
