<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="sala-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cinema_id')->textInput() ?>

    <?= $form->field($model, 'numero')->textInput() ?>

    <?= $form->field($model, 'num_filas')->textInput() ?>

    <?= $form->field($model, 'num_colunas')->textInput() ?>

    <?= $form->field($model, 'preco_bilhete')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'ativa' => 'Ativa', 'encerrada' => 'Encerrada', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
