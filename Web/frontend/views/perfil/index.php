<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Perfil';
?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0">O meu perfil</h4>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="box-white rounded-4 border shadow-sm">
                <div>
                    <h5 class="page-title mb-3">Dados pessoais</h5>
                    <div>

                        <?php $form = ActiveForm::begin([
                            'fieldConfig' => [
                                'options' => ['class' => 'mb-2'],
                            ]
                        ]); ?>

                        <div class="mb-4">
                            <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'username')->textInput(['disabled' => !$edit]) ?>
                            <?= $form->field($model->profile, 'nome')->textInput(['disabled' => !$edit]) ?>
                            <?= $form->field($model, 'email')->textInput(['disabled' => !$edit]) ?>
                            <?= $form->field($model->profile, 'telemovel')->textInput(['disabled' => !$edit]) ?>
                            <?= $edit ? $form->field($model, 'password')->passwordInput(['placeholder' => '(opcional)']) : '' ?>
                        </div>

                        <div class="d-flex gap-2">
                            <?php if ($edit): ?>
                                <?= Html::submitButton('Guardar', ['class' => 'btn btn-success rounded-3 w-100']) ?>
                                <?= Html::a('Cancelar', ['index'],
                                    ['class' => 'btn btn-light rounded-3 w-100',
                                        'style' => 'background-color: var(--gray-200);']
                                ) ?>
                            <?php else: ?>
                                <?= Html::a('Editar', ['index', 'edit' => 1],
                                    ['class' => 'btn btn-light rounded-3 w-100',
                                    'style' => 'background-color: var(--gray-200);']
                                ) ?>
                                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'p-0 m-0 w-100']) ?>
                                    <button type="submit" class="btn btn-danger rounded-3 w-100">Logout</button>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($edit): ?>
                        <div class="mt-2">
                            <?= Html::beginForm(['delete-account'], 'post', [
                                'class' => 'w-100',
                                'data-confirm' => 'Tem a certeza que deseja eliminar a sua conta? Esta ação é irreversível.',
                            ]) ?>
                            <button type="submit" class="btn btn-danger rounded-3 w-100">Eliminar Conta</button>
                            <?= Html::endForm() ?>
                        </div>
                        <?php endif; ?>

                        <?php $form = ActiveForm::end(); ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 d-flex flex-column gap-3">

            <!-- COMPRAS -->
            <div class="box-gray rounded-4 shadow-sm"">
                <h5 class="page-title mb-3">Histórico de Compras</h5>
                <?php foreach($compras as $compra): ?>
                    <div class="box-white rounded-4 mb-2">
                        <div class="d-flex gap-0 gap-lg-3 w-100">

                            <div>
                                <?= Html::img($compra->sessao->filme->getPosterUrl(), [
                                    'class' => 'd-none d-lg-block img-fluid rounded-3 shadow-sm object-fit-cover',
                                    'style' => 'aspect-ratio: 2/3; height: 112px; object-fit: cover;',
                                    'alt' => $compra->sessao->filme->titulo,
                                ]) ?>
                            </div>

                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <p class="mb-0 fw-semibold"><?= $compra->sessao->filme->titulo ?> • <?= $compra->labelBilhetes ?></p>
                                        <span class="fs-14 text-muted"><?= $compra->sessao->cinema->nome ?></span>
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-0 fw-semibold"><?= $compra->totalEmEuros ?></p>
                                        <span class="fs-14 text-muted">Efetuada a <?= $compra->dataFormatada ?></span>
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
                <a href="<?= Url::to(['compra/index']) ?>" class="btn btn-dark rounded-3 mt-1 w-100">Ver todas</a>
            </div>

            <!-- ALUGUERES -->
            <div class="box-gray rounded-4 shadow-sm"">
                <h5 class="page-title mb-3">Alugueres de Sala</h5>
                <?php foreach($alugueres as $aluguer): ?>
                    <div class="box-white rounded-4 mb-2">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="mb-0 fw-semibold"><?= $aluguer->cinema->nome ?></p>
                                <span class="fs-14 text-muted"><?= $aluguer->sala->nome ?></span>
                            </div>
                            <div class="text-end">
                                <p class="mb-0 fw-semibold"><?= $aluguer->estadoFormatado ?></p>
                                <span class="fs-14 text-muted">Pedido #<?= $aluguer->id ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="w-100 d-flex gap-2 gap-sm-5">
                                <div>
                                    <p class="mb-0 fw-semibold">Data</p>
                                    <span class="fs-14 text-muted"><?= $aluguer->dataFormatada ?></span>
                                </div>
                                <div>
                                    <p class="mb-0 fw-semibold">Horário</p>
                                    <span class="fs-14 text-muted"><?= $aluguer->horaInicioFormatada . ' - ' . $aluguer->horaFimFormatada ?></span>
                                </div>
                            </div>
                            <a href="<?= Url::to(['aluguer-sala/view', 'id' => $aluguer->id]) ?>" class="btn btn-dark px-3 rounded-3">Detalhes</a>
                        </div>
                    </div>

                <?php endforeach; ?>
                <a href="<?= Url::to(['aluguer-sala/index']) ?>" class="btn btn-dark rounded-3 px-3 mt-1 w-100">Ver todos</a>
            </div>
        </div>
    </div>



</div>

