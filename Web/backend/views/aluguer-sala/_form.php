<?php

use common\helpers\Formatter;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var yii\web\View $this */
/* @var common\models\AluguerSala $model */
/* @var yii\bootstrap4\ActiveForm $form */
/* @var bool $gerirAlugueres */
/* @var bool $gerirAlugueresCinema */
/* @var array $salaOptions */
/* @var array $estadoOptions */

?>

<div class="aluguer-sala-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cliente_id')->textInput(['value' => $model->cliente->profile->nome, 'disabled' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['value' => $model->cliente->email, 'disabled' => true])->label('Email') ?>
    <?= $form->field($model, 'telemovel')->textInput(['value' => $model->cliente->profile->telemovel, 'disabled' => true])->label('Telemóvel') ?>

    <?php if ($gerirAlugueres): ?>
        <?= $form->field($model, 'cinema_id')->textInput(['value' => $model->cinema->nome, 'disabled' => true]) ?>
    <?php endif ?>

    <?= $form->field($model, 'data')->textInput(['value' => Formatter::data($model->data), 'disabled' => true]) ?>
    <?= $form->field($model, 'horario')->textInput(['value' => $model->horario, 'disabled' => true,]) ?>
    <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true, 'disabled' => true]) ?>
    <?= $form->field($model, 'observacoes')->textarea(['rows' => 4, 'disabled' => true]) ?>
    <?= $form->field($model, 'sala_id')->dropDownList($salaOptions, ['prompt' => 'Selecione uma sala', 'disabled' => !$model->isEditable()]) ?>
    <?= $form->field($model, 'estado')->dropDownList($estadoOptions, ['disabled' => !$model->isEditable()]) ?>

    <?php if (($gerirAlugueres || $gerirAlugueresCinema) && $model->isEditable()): ?>
    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>
</div>
