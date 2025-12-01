<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $cinemaOptions array */
/* @var $gerirSalas bool */
/* @var $gerirSalasCinema bool */
/* @var $userCinemaId int */
/* @var $proximoNumero int|null */

$proximoNumero = null;

?>

<div class="sala-form">

    <!-- Form GET (Escolher o cinema) -->
    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['create']]); ?>

    <?php if ($gerirSalas): ?>
        <?= $form->field($model, 'cinema_id')->dropDownList($cinemaOptions, [
            'name' => 'cinema_id',
            'prompt' => 'Selecione o cinema',
            'onchange' => 'this.form.submit()',
            'disabled' => !$model->isNewRecord,
        ]) ?>
    <?php elseif ($gerirSalasCinema): ?>
        <?= Html::activeHiddenInput($model, 'cinema_id', ['value' => $userCinemaId]) ?>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>

    <!-- Form POST (Criação da sala) -->
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->cinema_id): ?>
        <?= $form->field($model, 'cinema_id')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <?php if ($proximoNumero && $model->isNewRecord): ?>
        <?= $form->field($model, 'numero')->textInput(['value' => $proximoNumero]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'num_filas')->textInput(['disabled' => !$model->isClosable() && !$model->isNewRecord]) ?>
    <?= $form->field($model, 'num_colunas')->textInput(['disabled' => !$model->isClosable() && !$model->isNewRecord]) ?>
    <?= $form->field($model, 'preco_bilhete')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'estado')->dropDownList($model::optsEstado(),
        ['disabled' => !$model->isClosable() && !$model->isNewRecord]
    ) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
