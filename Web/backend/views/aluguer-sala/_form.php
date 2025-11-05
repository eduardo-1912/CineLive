<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AluguerSala */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="aluguer-sala-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cliente_id')->textInput() ?>

    <?= $form->field($model, 'sala_id')->textInput() ?>

    <?= $form->field($model, 'data')->textInput() ?>

    <?= $form->field($model, 'hora_inicio')->textInput() ?>

    <?= $form->field($model, 'hora_fim')->textInput() ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'pendente' => 'Pendente', 'confirmado' => 'Confirmado', 'cancelado' => 'Cancelado', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'observacoes')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
