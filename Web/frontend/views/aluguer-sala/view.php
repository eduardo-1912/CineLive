<?php

use common\components\Formatter;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\AluguerSala $model */

$this->title = $model->nome;

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0"><?= $this->title ?></h4>
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

        <div class="box-white rounded-4 shadow-sm p-4 mb-4">
            <h5 class="page-title mb-3">Dados do Pedido</h5>

            <div class="w-100 d-flex flex-column">
                <div class="row row-cols-2 row-cols-md-3 w-100 gy-3">
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('tipo_evento') ?></span>
                        <span class="text-muted"><?= $model->tipo_evento ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('cinema') ?></span>
                        <span class="text-muted"><?= $model->cinema->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('sala') ?></span>
                        <span class="text-muted"><?= $model->sala->nome ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('data') ?></span>
                        <span class="text-muted"><?= Formatter::data($model->data) ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('horario') ?></span>
                        <span class="text-muted"><?= $model->horario ?></span>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span class="fw-semibold fs-14"><?= $model->getAttributeLabel('estado') ?></span>
                        <span class="text-muted"><?= $model->displayEstado() ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-white rounded-4 shadow-sm p-4">
            <h5 class="page-title mb-3"><?= $model->getAttributeLabel('observacoes') ?></h5>
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
