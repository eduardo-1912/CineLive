<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $mapaLugares */

?>

<div>
    <!-- Badges -->
    <div class="d-flex gap-2 mb-3 justify-content-center">
        <?php $badgeClasses = 'badge border shadow-sm rounded-pill px-2' ?>
        <h6><span class="<?= $badgeClasses ?> bg-light text-black">Disponível</span></h6>
        <h6><span class="<?= $badgeClasses ?> bg-danger">Selecionado</span></h6>
        <h6><span class="<?= $badgeClasses ?>" style="background-color: #9ca2a7;">Ocupado</span></h6>
    </div>

    <!-- Mapa Lugares -->
    <div class="text-center">

        <!-- Ecrã -->
        <div class="d-flex flex-column align-items-center justify-content-center mb-4">
            <p class="fw-semibold mb-1">Ecrã</p>
            <div style="height: 4px; max-width: 32rem; background-color: var(--gray-900);" class="rounded-pill w-100"></div>
        </div>

        <!-- Lugares -->
        <div class="d-inline-block" style="display: block; overflow-x: auto; white-space: nowrap; max-width: 100%;">
            <div class="d-inline-block text-center">
                <?php foreach ($mapaLugares as $fila => $colunas): ?>
                    <div class="d-flex justify-content-center align-items-center mb-2 flex-nowrap">

                        <!-- Letra da fila -->
                        <div class="fw-bold me-2" style="min-width: 20px;">
                            <?= chr(64 + $fila) ?>
                        </div>

                        <?php foreach ($colunas as $lugar): ?>

                            <?php
                            $classes = 'lugar d-flex align-items-center rounded-3 shadow-sm justify-content-center btn fw-semibold mx-1 border ';

                            if ($lugar['ocupado']) {
                                $classes .= 'btn-secondary disabled pe-none';
                            }
                            elseif ($lugar['selecionado']) {
                                $classes .= 'btn-danger';
                            }
                            else {
                                $classes .= 'btn-light';
                            }
                            ?>

                            <?= Html::a($lugar['label'], $lugar['url'], ['class' => $classes]) ?>

                        <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>
