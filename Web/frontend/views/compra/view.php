<?php

use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Compra $model */

$this->title = 'Compra #' . $model->id;

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0">Compra #<?= $model->id ?></h4>
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
        <!-- DETALHES DA COMPRA -->
        <div class="box-white rounded-4 shadow-sm p-4 mb-4">

        <div class="d-flex gap-3 align-items-start">

            <!-- POSTER -->
            <?= Html::img($model->sessao->filme->getPosterUrl(), [
                'class' => 'd-none d-md-block img-fluid rounded-3 shadow-sm',
                'style' => 'height: 147px; aspect-ratio: 2/3; object-fit: cover;',
                'alt' => $model->sessao->filme->titulo,
            ]) ?>

            <!-- INFO -->
            <div class="w-100 d-flex flex-column gap-2">
                <h4 class="mb-1"><?= $model->sessao->filme->titulo  . ' • ' . $model->dataFormatada ?></h4>

                <div class="row row-cols-2 row-cols-md-3 w-100 gy-3">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold">Cinema</span>
                        <span class="text-muted"><?= $model->sessao->cinema->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold">Sala</span>
                        <span class="text-muted"><?= $model->sessao->sala->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold">Estado</span>
                        <span class="text-muted"><?= $model->estadoFormatado ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold">Data da Sessão</span>
                        <span class="text-muted"><?= $model->sessao->dataFormatada ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold">Horário</span>
                        <span class="text-muted"><?= $model->sessao->horario ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-medium fs-14 fw-semibold">Total</span>
                        <span class="text-muted"><?= $model->totalEmEuros ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>

        <!-- TABELA DE BILHETES -->
        <div class="box-white rounded-4 shadow-sm p-4">
        <h5 class="page-title mb-3">Bilhetes</h5>

        <div class="table-responsive">
            <table class="table bg-white table-striped align-middle">
                <thead>
                <tr>
                    <th class="text-start">Código</th>
                    <th class="text-start">Lugar</th>
                    <th class="text-start">Preço</th>
                    <th class="text-start">Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->bilhetes as $bilhete): ?>
                    <tr>
                        <td><?= $bilhete->codigo ?></td>
                        <td><?= $bilhete->lugar ?></td>
                        <td><?= $bilhete->precoEmEuros ?></td>
                        <td><?= $bilhete->estadoFormatado ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
    </div>
</div>
