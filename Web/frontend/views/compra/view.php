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

    <!-- 🟦 DETALHES DA COMPRA -->
    <div class="box-gray rounded-4 shadow-sm p-4 mb-4">

        <div class="d-flex gap-4 align-items-start">

            <!-- POSTER -->
            <?= Html::img($model->sessao->filme->getPosterUrl(), [
                'class' => 'd-none d-md-block img-fluid rounded-3 shadow-sm',
                'style' => 'width: 130px; aspect-ratio: 2/3; object-fit: cover;',
                'alt' => $model->sessao->filme->titulo,
            ]) ?>

            <!-- INFO -->
            <div class="w-100 d-flex flex-column gap-2">

                <h5 class="fw-semibold mb-1"><?= $model->sessao->filme->titulo ?></h5>

                <div class="d-flex flex-column fs-14 text-muted">
                    <span><strong>Cinema:</strong> <?= $model->sessao->cinema->nome ?></span>
                    <span><strong>Data da Compra:</strong> <?= $model->dataFormatada ?></span>
                    <span><strong>Total:</strong> <?= $model->totalEmEuros ?></span>
                    <span><strong>Pagamento:</strong> <?= $model->pagamento ?></span>
                    <span><strong>Estado:</strong> <?= $model->estadoFormatado ?></span>
                </div>

                <div class="d-flex flex-column mt-2 fs-14">
                    <strong class="mb-1">Sessão</strong>
                    <span><?= $model->sessao->dataFormatada ?> • <?= $model->sessao->horaInicioFormatada ?></span>
                    <span><strong>Sala:</strong> <?= $model->sessao->sala->nome ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 🟩 TABELA DE BILHETES -->
    <div class="box-gray rounded-4 shadow-sm p-4">
        <h5 class="page-title mb-3">Bilhetes</h5>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
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
                        <td><?= number_format($bilhete->preco, 2) ?>€</td>
                        <td><?= $bilhete->estado ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>
