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
$emailCliente = $model->cliente->email;
$telemovelCliente = $model->cliente->profile->telemovel;
$nomeCinema = $model->cinema->nome ?? '-';

$salasDisponiveis = Sala::getSalasDisponiveis(
    $model->cinema_id,
    $model->data,
    $model->hora_inicio,
    $model->hora_fim
);
$salasDisponiveis[] = $model->sala;

?>

<div class="aluguer-sala-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- NOME CLIENTE -->
    <?= $form->field($model, 'cliente_id')->textInput(['value' => $nomeCliente, 'disabled' => true]) ?>

    <div class="<?= $isAdmin ? 'd-block' : 'd-none' ?> ">
        <?= $form->field($model, 'cinema_id')->textInput(['value' => $nomeCinema, 'disabled' => true]) ?>
    </div>

    <!-- DATA -->
    <?= $form->field($model, 'data')->input('date', ['value' => $model->data, 'disabled' => true]) ?>

    <!-- HORA INÍCIO/FIM -->
    <?= $form->field($model, 'hora_inicio')->input('time', ['value' => $model->hora_inicio, 'disabled' => true,]) ?>

    <?= $form->field($model, 'hora_fim')->input('time', ['value' => $model->hora_fim, 'disabled' => true,]) ?>

    <!-- TIPO EVENTO-->
    <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true, 'disabled' => true]) ?>

    <!-- OBSERVAÇÕES -->
    <?= $form->field($model, 'observacoes')->textarea(['rows' => 4, 'disabled' => true]) ?>


    <?= $form->field($model, 'sala_id')->dropDownList(
        ArrayHelper::map($salasDisponiveis, 'id', 'nome'),
        ['prompt' => 'Selecione uma sala']
    ) ?>

    <?= $form->field($model, 'estado')->dropDownList($model->optsEstadoBD()) ?>

    <div class="form-group">
        <?= Html::submitButton('Confirmar', ['class' => 'btn btn-success']) ?>
        <a href="mailto:<?=$emailCliente?>" class="btn btn-primary" target="_blank">Email</a>
    </div>


    <?php ActiveForm::end(); ?>

</div>
