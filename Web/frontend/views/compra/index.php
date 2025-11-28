<?php

use yii\bootstrap4\Breadcrumbs;

/** @var common\models\Compra $compras */
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
        <div class="d-flex flex-column gap-3">
            <?php if ($compras): ?>
                <?php foreach ($compras as $compra): ?>
                    <?= $this->render('@frontend/views/compra/_card', ['compra' => $compra]) ?>
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

