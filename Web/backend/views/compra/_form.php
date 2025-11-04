<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Compra */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="compra-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cliente_id')->textInput() ?>

    <?= $form->field($model, 'data')->textInput() ?>

    <?= $form->field($model, 'pagamento')->dropDownList([ 'mbway' => 'Mbway', 'cartao' => 'Cartao', 'multibanco' => 'Multibanco', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'pendente' => 'Pendente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
