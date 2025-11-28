<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var common\models\User $model */
/** @var common\models\Compra $compras */
/** @var common\models\AluguerSala $alugueres */
/** @var yii\web\View $this */

$this->title = 'Perfil';
?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0">O meu Perfil</h4>
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
                            <?= $form->field($model->profile, 'telemovel')->Input('number', ['maxlength' => true, 'disabled' => !$edit]) ?>
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
                                <a href="<?= Url::to(['/site/logout']) ?>" data-method="post" class="btn btn-danger rounded-3 w-100">Logout</a>
                            <?php endif; ?>
                        </div>

                        <?php $form = ActiveForm::end(); ?>

                        <?php if ($edit): ?>
                            <div class="mt-2">
                                <?= Html::a('Eliminar Conta', ['delete-account'], [
                                    'class' => 'btn btn-danger rounded-3 w-100',
                                    'data-method' => 'post',
                                    'data-confirm' => 'Tem a certeza que deseja eliminar a sua conta? Esta ação é irreversível.',
                                ]) ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 d-flex flex-column gap-3">

            <!-- COMPRAS -->
            <div class="box-gray rounded-4 shadow-sm"">
                <h5 class="page-title mb-3">Histórico de compras</h5>
            <div class="d-flex flex-column gap-3">
                <?php if ($compras): ?>
                    <?php foreach ($compras as $compra): ?>
                        <?= $this->render('@frontend/views/compra/_card', ['compra' => $compra]) ?>
                    <?php endforeach; ?>
                    <a href="<?= Url::to(['compra/index']) ?>" class="btn btn-dark rounded-3 mt-1 w-100">Ver todas</a>
                <?php else: ?>
                    <div class="box-white rounded-4">
                        <div class="d-flex justify-content-center align-items-center w-100" style="height: 6.15rem;">
                            <h5 class="text-muted text-center fw-semibold m-0">Nenhuma compra encontrada!</h5>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            </div>

            <!-- ALUGUERES -->
            <div class="box-gray rounded-4 shadow-sm"">
                <h5 class="page-title mb-3">Alugueres de sala</h5>
                <div class="d-flex flex-column gap-3">
                    <?php if ($alugueres): ?>

                        <?php foreach($alugueres as $aluguer): ?>
                            <?= $this->render('@frontend/views/aluguer-sala/_card', ['aluguer' => $aluguer]) ?>
                        <?php endforeach; ?>
                        <a href="<?= Url::to(['aluguer-sala/index']) ?>" class="btn btn-dark rounded-3 px-3 mt-1 w-100">Ver todos</a>

                    <?php else: ?>

                        <div class="box-white rounded-4">
                            <div class="d-flex justify-content-center align-items-center w-100" style="height: 6.15rem;">
                                <h5 class="text-muted text-center fw-semibold m-0">Nenhum pedido encontrado!</h5>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



</div>

