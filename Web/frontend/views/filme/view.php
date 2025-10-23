<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */

$this->title = $model->titulo;
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <?= Html::img($model->getPosterUrl(), [
                'class' => 'img-fluid rounded shadow-sm',
                'alt' => $model->titulo,
            ]) ?>
        </div>

        <div class="col-md-8">
            <h1><?= Html::encode($model->titulo) ?></h1>
            <p><strong>Realização:</strong> <?= Html::encode($model->realizacao) ?></p>
            <p><strong>Duração:</strong> <?= Html::encode($model->duracao) ?> min</p>
            <p><strong>Idioma:</strong> <?= Html::encode($model->idioma) ?></p>
            <p><strong>Estreia:</strong> <?= Yii::$app->formatter->asDate($model->estreia) ?></p>

            <h5 class="mt-4">Sinopse</h5>
            <p><?= nl2br(Html::encode($model->sinopse)) ?></p>

            <?php if ($model->trailer_url): ?>
                <div class="mt-4">
                    <h5>Trailer</h5>
                    <iframe width="100%" height="315"
                            src="<?= str_replace('watch?v=', 'embed/', $model->trailer_url) ?>"
                            frameborder="0"
                            allowfullscreen>
                    </iframe>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <?= Html::a('← Voltar à lista', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
