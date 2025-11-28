<?php

use yii\bootstrap4\Breadcrumbs;

/** @var common\models\AluguerSala $alugueres */
/** @var yii\web\View $this */

$this->title = 'Alugueres';

?>

<div class="container">

    <div class="mb-4 d-flex justify-content-between">
        <h4 class="page-title m-0">Os meus alugueres</h4>
        <?= Breadcrumbs::widget([
            'links' => [
                ['label' => 'Perfil', 'url' => ['perfil/index']],
                ['label' => 'Alugueres'],
            ],
            'homeLink' => false,
            'options' => ['class' => 'breadcrumb'],
        ]) ?>
    </div>

    <div class="box-gray shadow-sm w-100">
        <div class="d-flex flex-column gap-3">
            <?php if ($alugueres): ?>
                <?php foreach($alugueres as $aluguer): ?>
                    <?= $this->render('@frontend/views/aluguer-sala/_card', ['aluguer' => $aluguer]) ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="box-white rounded-4">
                    <div class="d-flex justify-content-center align-items-center w-100" style="height: 50vh;">
                        <h5 class="text-muted text-center fw-semibold m-0">Nenhum pedido encontrado!</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

