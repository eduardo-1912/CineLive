<?php

use common\models\Cinema;
use common\models\Sala;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AluguerSala */
/* @var $form yii\bootstrap4\ActiveForm */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');

$nomeCliente = $model->cliente->profile->nome ?? $model->cliente->username;
$nomeCinema = $model->cinema->nome ?? '-';

?>

<div class="aluguer-sala-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- NOME CLIENTE -->
    <?= $form->field($model, 'cliente_id')->textInput(['value' => $nomeCliente, 'disabled' => true]) ?>

    <div class="<?= $isAdmin ? 'd-block' : 'd-none' ?> ">
        <?= $form->field($model, 'cinema_id')->textInput(['value' => $nomeCinema, 'disabled' => true]) ?>
    </div>

    <?= $form->field($model, 'sala_id')->dropDownList(
        ArrayHelper::map(Sala::find()->where(['cinema_id' => $model->cinema_id, 'estado' => Sala::ESTADO_ATIVA])
        ->orderBy('numero')->all(), 'id', 'nome'), ['prompt' => 'Selecione a sala']) ?>


    <!-- DATA -->
    <?= $form->field($model, 'data')->input('date', ['value' => $model->data, 'disabled' => true]) ?>

    <!-- HORA INÍCIO/FIM -->
    <?= $form->field($model, 'hora_inicio')->input('time', ['value' => $model->hora_inicio, 'disabled' => true,]) ?>

    <?= $form->field($model, 'hora_fim')->input('time', ['value' => $model->hora_fim, 'disabled' => true,]) ?>

    <!-- TIPO EVENTO-->
    <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true, 'disabled' => true]) ?>

    <!-- OBSERVAÇÕES -->
    <?= $form->field($model, 'observacoes')->textarea(['rows' => 4, 'disabled' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
