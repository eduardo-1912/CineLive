<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Compras';

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0">As minhas compras</h4>
        <?= Breadcrumbs::widget([
            'links' => [
                ['label' => 'Perfil', 'url' => ['perfil/index']],
                ['label' => 'Compras'],
            ],
            'homeLink' => false,
            'options' => ['class' => 'breadcrumb'],
        ]) ?>
    </div>

    <div class="box-gray shadow-sm w-100">
        <div class="d-flex flex-column gap-2">
            <?php if ($compras): ?>
                <?php foreach($compras as $compra): ?>
                <div class="box-white rounded-4">
                    <div class="d-flex gap-3 w-100">

                    <div>
                        <?= Html::img($compra->sessao->filme->getPosterUrl(), [
                            'class' => 'd-none d-md-block img-fluid rounded-3 shadow-sm object-fit-cover',
                            'style' => 'aspect-ratio: 2/3; height: 112px; object-fit: cover;',
                            'alt' => $compra->sessao->filme->titulo,
                        ]) ?>
                    </div>

                    <div class="w-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="mb-0 fw-semibold"><?= $compra->sessao->filme->titulo ?> • <?= $compra->dataFormatada ?></p>
                                <span class="fs-14 text-muted"><?= $compra->sessao->cinema->nome ?></span>
                            </div>
                            <div class="text-end">
                                <p class="mb-0 fw-semibold"><?= $compra->estadoFormatado ?></p>
                                <span class="fs-14 text-muted"><?= $compra->totalEmEuros ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                        <div class="w-100 d-flex gap-5">
                            <div>
                                <p class="mb-0 fw-semibold">Sessão</p>
                                <span class="fs-14 text-muted"><?= $compra->sessao->dataFormatada . ' - ' . $compra->sessao->horaInicioFormatada ?></span>
                            </div>
                            <div class="d-none d-sm-block">
                                <p class="mb-0 fw-semibold">Lugares</p>
                                <span class="fs-14 text-muted"><?= $compra->listaLugares ?></span>
                            </div>
                        </div>
                        <a href="<?= Url::to(['compra/view', 'id' => $compra->id]) ?>" class="btn btn-dark px-3 rounded-3">Detalhes</a>
                    </div>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="box-white rounded-4">
                    <div class="d-flex justify-content-center align-items-center w-100" style="height: 50vh;">
                        <h5 class="text-muted text-center fw-semibold m-0">Nenhuma compra encontrada!</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

