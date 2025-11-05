<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Compra */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="compra-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cliente_id')->textInput([
        'value' => $model->cliente->profile->nome,
        'disabled' => true]) ?>


    <?= $form->field($model, 'data')->textInput([
        'value' => $model->dataFormatada,
        'disabled' => 'true']) ?>

    <?= $form->field($model, 'pagamento')->dropDownList($model::optsPagamento(), ['disabled' => 'true']) ?>

    <?= $form->field($model, 'estado')->dropDownList($model::optsEstado()) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
