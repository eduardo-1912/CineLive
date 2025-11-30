<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var bool $edit */
/** @var common\models\Compra $compras */
/** @var common\models\AluguerSala $alugueres */

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
                    <?= $this->render('_form', ['model' => $model, 'edit' => $edit]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-8 d-flex flex-column gap-3">

            <!-- Compras -->
            <div class="box-gray rounded-4 shadow-sm"">
                <h5 class="page-title mb-3">Histórico de compras</h5>
            <div class="d-flex flex-column gap-3">
                <?php if ($compras): ?>
                    <?php foreach ($compras as $compra): ?>
                        <?= $this->render('@frontend/views/compra/_card', ['compra' => $compra]) ?>
                    <?php endforeach; ?>
                    <a href="<?= Url::to(['compra/index']) ?>" class="btn btn-dark rounded-3 mt-1 w-100">Ver Todas</a>
                <?php else: ?>
                    <div class="box-white rounded-4">
                        <div class="d-flex justify-content-center align-items-center w-100" style="height: 6.15rem;">
                            <h5 class="text-muted text-center fw-semibold m-0">Nenhuma compra encontrada!</h5>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            </div>

            <!-- Alugueres -->
            <div class="box-gray rounded-4 shadow-sm"">
                <h5 class="page-title mb-3">Alugueres de sala</h5>
                <div class="d-flex flex-column gap-3">
                    <?php if ($alugueres): ?>

                        <?php foreach($alugueres as $aluguer): ?>
                            <?= $this->render('@frontend/views/aluguer-sala/_card', ['aluguer' => $aluguer]) ?>
                        <?php endforeach; ?>
                        <a href="<?= Url::to(['aluguer-sala/index']) ?>" class="btn btn-dark rounded-3 px-3 mt-1 w-100">Ver Todos</a>

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


