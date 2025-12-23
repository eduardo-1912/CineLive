<?php

use common\helpers\Formatter;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Compra $model */

$this->title = $model->nome;

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0"><?= $this->title ?></h4>
        <?= Breadcrumbs::widget([
            'links' => [
                ['label' => 'Perfil', 'url' => ['perfil/index']],
                ['label' => 'Compras', 'url' => ['compra/index']],
                ['label' => $model->id],
            ],
            'homeLink' => false,
            'options' => ['class' => 'breadcrumb'],
        ]) ?>
    </div>

    <div class="box-gray">
        <div class="box-white rounded-4 shadow-sm p-4 mb-4">
            <div class="d-flex gap-3 align-items-start">

            <?= Html::img($model->sessao->filme->posterUrl, [
                'class' => 'd-none d-md-block img-fluid rounded-3 shadow-sm',
                'style' => 'height: 147px; aspect-ratio: 2/3; object-fit: cover;',
                'alt' => $model->sessao->filme->titulo,
            ]) ?>

            <div class="w-100 d-flex flex-column gap-2">
                <h4 class="mb-1"><?= "{$model->sessao->filme->titulo} • {$model->displayEstado()}" ?></h4>

                <div class="row row-cols-2 row-cols-md-3 w-100 gy-3">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('data') ?></span>
                        <span class="text-muted"><?= Formatter::data($model->data) ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('cinema') ?></span>
                        <span class="text-muted"><?= $model->sessao->cinema->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('sala') ?></span>
                        <span class="text-muted"><?= $model->sessao->sala->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('sessao') ?></span>
                        <span class="text-muted"><?= Formatter::data($model->sessao->data) ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('horario') ?></span>
                        <span class="text-muted"><?= $model->sessao->horario ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('total') ?></span>
                        <span class="text-muted"><?= Formatter::preco($model->total) ?></span>
                    </div>
                </div>

            </div>
        </div>
        </div>

        <div class="box-white rounded-4 shadow-sm p-4">
            <h5 class="page-title mb-3"><?= $model->getAttributeLabel('bilhetes') ?></h5>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gy-4">

                <?php foreach ($model->bilhetes as $bilhete): ?>
                <div>
                    <div class="box-white bg-light rounded-4 d-flex justify-content-between mb-2">
                        <div class="d-flex flex-column text-start">
                            <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('bilhetes.lugar') ?></span>
                            <span class="text-muted"><?= $bilhete->lugar ?></span>
                        </div>

                        <div class="d-flex flex-column text-center">
                            <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('bilhetes.estado') ?></span>
                            <span class="text-muted"><?= $bilhete->estadoHtml ?></span>
                        </div>

                        <div class="d-flex flex-column text-end">
                            <span class="fw-medium fs-14 fw-semibold"><?= $model->getAttributeLabel('bilhetes.preco') ?></span>
                            <span class="text-muted"><?= Formatter::preco($bilhete->preco) ?></span>
                        </div>
                    </div>

                    <div class="box-gray d-flex flex-column align-items-center rounded-4">
                        <h3><?= $bilhete->codigo ?></h3>
                        <?= Html::img(
                            'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($bilhete->codigo),
                            ['alt' => 'QR Code do bilhete', 'class' => 'img-fluid bg-white p-3 rounded-3']
                        ) ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>

        </div>

    </div>
</div>
