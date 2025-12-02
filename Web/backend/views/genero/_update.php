<?php

use yii\helpers\Html;

/** @var $model common\models\Genero */
?>

<?= Html::beginForm(['genero/update', 'id' => $model->id], 'post', [
    'class' => 'd-inline-flex gap-1',
]) ?>

<?= Html::textInput('Genero[nome]', $model->nome, [
    'class' => 'form-control form-control-sm',
    'style' => 'width: 20rem;',
]) ?>

<?= Html::submitButton('<i class="fas fa-edit"></i>', [
    'class' => 'btn btn-warning btn-sm',
]) ?>

<?= Html::endForm() ?>
