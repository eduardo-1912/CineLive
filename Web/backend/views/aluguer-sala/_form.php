<?php

use common\models\AluguerSala;
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

$nomeCliente = $model->cliente->profile->nome ?? '-';
$emailCliente = $model->cliente->email ?? '-';
$telemovelCliente = $model->cliente->profile->telemovel ?? '-';
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

    <?= $form->field($model, 'cliente_id')->textInput(['value' => $nomeCliente, 'disabled' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['value' => $emailCliente, 'disabled' => true,])->label('Email') ?>
    <?= $form->field($model, 'telemovel')->textInput(['value' => $telemovelCliente, 'disabled' => true,])->label('Telemóvel') ?>

    <div class="<?= $isAdmin ? 'd-block' : 'd-none' ?> ">
        <?= $form->field($model, 'cinema_id')->textInput(['value' => $nomeCinema, 'disabled' => true]) ?>
    </div>

    <!-- HORA INÍCIO/FIM -->
    <?= $form->field($model, 'horario')->textInput
    (['value' => $model->dataFormatada . ' | ' . $model->horaInicioFormatada . ' - ' . $model->horaFimFormatada, 'disabled' => true,])
    ->label('Horário') ?>

    <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true, 'disabled' => true]) ?>
    <?= $form->field($model, 'observacoes')->textarea(['rows' => 4, 'disabled' => true]) ?>

    <!-- SALA -->
    <?= $form->field($model, 'sala_id')->dropDownList(
        ArrayHelper::map($salasDisponiveis, 'id', 'nome'),
        [
            'prompt' => 'Selecione uma sala',
            'disabled' => in_array($model->estado, [
                $model::ESTADO_A_DECORRER,
                $model::ESTADO_TERMINADO,
            ]),
        ]
    ) ?>


    <!-- ESTADO -->
    <?php
    $estados = $model->optsEstadoBD();

    if ($model->estado === $model::ESTADO_CONFIRMADO) {
        unset($estados[$model::ESTADO_PENDENTE]);
    }

    if (!isset($estados[$model->estado])) {
        $estados[$model->estado] = $model->displayEstado();
    }

    echo $form->field($model, 'estado')->dropDownList(
        $estados,
        [
            'disabled' => in_array($model->estado, [
                $model::ESTADO_A_DECORRER,
                $model::ESTADO_TERMINADO,
                $model::ESTADO_CANCELADO,
            ]),
        ]
    );
    ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
