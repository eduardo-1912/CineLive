<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Bilhete */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="bilhete-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'compra_id')->textInput() ?>

    <?= $form->field($model, 'sessao_id')->textInput() ?>

    <?= $form->field($model, 'lugar')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'preco')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'pendente' => 'Pendente', 'confirmado' => 'Confirmado', 'cancelado' => 'Cancelado', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
