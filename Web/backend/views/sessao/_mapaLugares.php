<?php

use yii\helpers\Html;

/** @var common\models\Sessao $model */
/* @var $mapaLugares array */

?>

<div class="text-center">
    <div class="mb-2 d-flex justify-content-center gap-3">
        <h5><span class="badge bg-secondary opacity-75">Disponível</span></h5>
        <h5><span class="badge bg-info">Ocupado</span></h5>
        <h5><span class="badge bg-danger">Confirmado</span></h5>
    </div>

    <div class="d-inline-block bg-light p-4 rounded-4 shadow-sm">
        <?php foreach ($mapaLugares as $fila => $colunas): ?>
            <div class="d-flex justify-content-center mb-2 flex-wrap">

                <?php foreach ($colunas as $lugar): ?>

                    <?php
                    $classes = 'lugar fw-semibold text-center rounded-3 mx-2 my-0 d-flex align-items-center justify-content-center ';

                    if ($lugar['confirmado']) {
                        $classes .= 'bg-danger';
                    }
                    elseif ($lugar['ocupado']) {
                        $classes .= 'bg-info';
                    }
                    else {
                        $classes .= 'bg-secondary opacity-75';
                    }
                    ?>

                    <?php if ($lugar['compraId']): ?>
                        <?= Html::a($lugar['label'], ['compra/view', 'id' => $lugar['compraId']], [
                            'class' => $classes . ' text-decoration-none',
                        ]) ?>
                    <?php else: ?>
                        <div class="<?= $classes ?>">
                            <?= $lugar['label'] ?>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>

            </div>
        <?php endforeach; ?>
    </div>

</div>
