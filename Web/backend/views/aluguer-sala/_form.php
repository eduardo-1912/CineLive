<?php

use common\models\AluguerSala;
use common\models\Cinema;
use common\models\Sala;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var yii\web\View $this */
/* @var common\models\AluguerSala $model */
/* @var yii\bootstrap4\ActiveForm $form */

?>

<div class="aluguer-sala-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cliente_id')->textInput(['value' => $nomeCliente, 'disabled' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['value' => $emailCliente, 'disabled' => true,])->label('Email') ?>
    <?= $form->field($model, 'telemovel')->textInput(['value' => $telemovelCliente, 'disabled' => true,])->label('Telemóvel') ?>

    <!-- CAMPO CINEMA APENAS PARA ADMIN -->
    <div class="<?= $isAdmin ? 'd-block' : 'd-none' ?> ">
        <?= $form->field($model, 'cinema_id')->textInput(['value' => $nomeCinema, 'disabled' => true]) ?>
    </div>

    <!-- DATA E HORAS -->
    <?= $form->field($model, 'horario')->textInput
    (['value' => $model->dataFormatada . ' | ' . $model->horaInicioFormatada . ' - ' . $model->horaFimFormatada, 'disabled' => true,])
    ->label('Horário') ?>

    <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true, 'disabled' => true]) ?>
    <?= $form->field($model, 'observacoes')->textarea(['rows' => 4, 'disabled' => true]) ?>

    <!-- SALAS -->
    <?= $form->field($model, 'sala_id')->dropDownList($salasDisponiveis,
        [
            'prompt' => 'Selecione uma sala',
            'disabled' => in_array($model->estado, [
                $model::ESTADO_A_DECORRER,
                $model::ESTADO_TERMINADO,
                $model::ESTADO_CANCELADO,
            ]),
        ]
    ) ?>


    <!-- ESTADO -->
    <?= $form->field($model, 'estado')->dropDownList(
        $model->getEstadoOptions(),
        [
            'disabled' => in_array($model->estado, [
                $model::ESTADO_A_DECORRER,
                $model::ESTADO_TERMINADO,
                $model::ESTADO_CANCELADO,
            ]),
        ]
    ) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
