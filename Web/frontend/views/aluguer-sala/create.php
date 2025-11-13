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

?>

<div class="aluguer-sala-form">
    <?php $form = ActiveForm::begin(['id' => 'aluguer-sala-form',
        'action' => ['AluguerSala/AluguerSalaForm'],
        'method' => 'post'
]); ?>

    <div class="container my-5">
        <div class="row min-vh-50">
            <div class="col-md-8">
                <h4 class="fw-bold">Preencha seus dados</h4>
                <p class="text-muted mb-4">
                    Faça seu pedido de Aluguer de sala aqui.
                </p>
                <?= $form->field($model, 'cliente_id')->textInput(['value' => $nomeCliente, 'disabled' => true]) ?>
                <?= $form->field($model, 'email')->textInput(['value' => $emailCliente, 'disabled' => true,])->label('Email') ?>
                <?= $form->field($model, 'telemovel')->textInput(['value' => $telemovelCliente, 'disabled' => true,])->label('Telemóvel') ?>
            </div>

            <!-- FORMULARIO 'GET' PARA SELECIONAR O CINEMA -->
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['create']]); ?>
            <div class="form-group">
                <label for="cinema_id">Cinema</label>
                <?= Html::dropDownList('cinema_id', $model->cinema_id, $cinemasOptions, [
                        'prompt' => 'Selecione o cinema',
                        'class' => 'form-control',
                        'onchange' => 'this.form.submit()',
                        'disabled' => !$model->isNewRecord,
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>

            <!-- FORM 'POST' PARA CRIAR PEDIDO -->

            <?php $form = ActiveForm::begin(); ?>

            <?php if ($model->cinema_id): ?>
                <?= $form->field($model, 'cinema_id')->hiddenInput(['value' => $model->cinema_id])->label(false) ?>
            <?php endif; ?>

            <?= $form->field($model, 'data')->Input('date')->label('Data') ?>

            <!-- HORA INÍCIO/FIM -->
            <?= $form->field($model, 'hora_inicio')->Input('time')->label('Horário de Inicio') ?>
            <?= $form->field($model, 'hora_fim')->Input('time')->label('Horário de Encerramento') ?>

            <!-- TIPO DE EVENTO -->
            <?= $form->field($model, 'tipo_evento')->textInput(['maxlength' => true]) ?>

            <!-- OBSERVAÕES -->
            <?= $form->field($model, 'observacoes')->textarea(['rows' => 4]) ?>

            <!-- SALA -->
            <?= $form->field($model, 'sala_id')->dropDownList($salasDisponiveis,
                [
                    'prompt' => 'Selecione uma sala',
                    'disabled' => in_array($model->estado, [
                        $model::ESTADO_A_DECORRER,
                        $model::ESTADO_TERMINADO,
                    ]),
                ]
            ) ?>

    <div class="form-group mt-4">
        <?= Html::submitButton('Enviar Pedido', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
