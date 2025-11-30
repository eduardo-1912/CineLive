<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Cinema */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="cinema-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nome')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rua')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigo_postal')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cidade')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'telefone')
        ->textInput(['type' => 'tel', 'maxlength' => 9, 'pattern' => '[0-9]{9}'
        ]) ?>

    <?= $form->field($model, 'horario_abertura')->input('time') ?>

    <?= $form->field($model, 'horario_fecho')->input('time') ?>

    <?php if ($gerirCinemas): ?>
        <?= $form->field($model, 'estado')->dropDownList($dropdownEstados,
            ['disabled' => (!$model->isClosable() && !$model->isEstadoEncerrado() && !$model->isNewRecord)]) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
