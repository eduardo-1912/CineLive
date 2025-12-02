<?php

use yii\helpers\Html;

/** @var common\models\Bilhete $model */

?>

<?= Html::beginForm(['bilhete/update-lugar', 'id' => $model->id], 'post', [
    'class' => 'd-inline-flex gap-1 align-items-center',
]) ?>

<?= Html::input('text', 'Bilhete[lugar]', $model->lugar, [
    'class' => 'form-control form-control-sm',
    'style' => 'width: 10rem',
    'disabled' => !$model->isEditable(),
]) ?>

<?= Html::submitButton('<i class="fas fa-edit"></i>', [
    'class' => 'btn btn-sm ' . (!$model->isEditable() ? 'btn-warning' : 'btn-secondary'),
    'title' => 'Guardar',
    'disabled' => !$model->isEditable(),
]) ?>

<?= Html::endForm() ?>
