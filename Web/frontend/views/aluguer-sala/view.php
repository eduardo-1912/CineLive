<?php

use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Compra $model */

$this->title = 'Aluguer #' . $model->id;

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0">Aluguer #<?= $model->id ?></h4>
        <?= Breadcrumbs::widget([
            'links' => [
                ['label' => 'Perfil', 'url' => ['perfil/index']],
                ['label' => 'Alugueres', 'url' => ['aluguer-sala/index']],
                ['label' => $model->id],
            ],
            'homeLink' => false,
            'options' => ['class' => 'breadcrumb'],
        ]) ?>
    </div>

    <div class="box-gray">
        <!-- DETALHES DO ALUGUER -->
        <div class="box-white rounded-4 shadow-sm p-4 mb-4">
            <h5 class="page-title mb-3">Dados do Pedido</h5>

            <div class="w-100 d-flex flex-column">
                <div class="row row-cols-2 row-cols-md-3 w-100 gy-3">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14">Tipo Evento</span>
                        <span class="text-muted"><?= $model->tipo_evento ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14">Cinema</span>
                        <span class="text-muted"><?= $model->cinema->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14">Sala</span>
                        <span class="text-muted"><?= $model->sala->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14">Data</span>
                        <span class="text-muted"><?= $model->dataFormatada ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14">Horário</span>
                        <span class="text-muted"><?= "{$model->horaInicioFormatada} - {$model->horaFimFormatada}" ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14">Estado</span>
                        <span class="text-muted"><?= $model->estadoFormatado ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-white rounded-4 shadow-sm p-4">
            <h5 class="page-title mb-3">Observações</h5>
            <div class="form-group mb-0">
                <?= Html::textarea('bio', $model->observacoes, [
                    'class' => 'form-control',
                    'rows' => 6,
                    'disabled' => true
                ]) ?>
            </div>
        </div>

        <?php if ($model->isDeletable()): ?>
            <?= Html::a('Eliminar Pedido', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger rounded-3 mt-3 w-100',
                'data-method' => 'post',
                'data-confirm' => 'Tem a certeza que deseja eliminar este pedido de aluguer? Esta ação é irreversível.',
            ]) ?>
        <?php endif; ?>
    </div>
</div>
