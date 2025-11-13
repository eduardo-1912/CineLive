<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;
use common\models\Sala;
use common\models\Filme;

?>

<div class="sessao-form">

    <!-- FORM GET PARA OBTER DADOS -->
    <?php $form = ActiveForm::begin(['method' => 'get',
        'action' => [$model->isNewRecord ? 'create' : 'update', 'id' => $model->id],
        'id' => 'sessao-form-get'
    ]); ?>

    <?php if ($gerirCinemas): ?>

        <!-- DROPDOWN DE CINEMAS -->
        <?= $form->field($model, 'cinema_id')->dropDownList($cinemasAtivos,
            [
                'prompt' => 'Selecione o cinema',
                'onchange' => 'this.form.submit()',
                'name' => 'cinema_id',
                'disabled' => !$model->isNewRecord,
            ]
        ) ?>

    <?php else: ?>

        <!-- HIDDEN-INPUT COM CINEMA DO GERENTE -->
        <?= Html::hiddenInput('cinema_id', $userCinemaId) ?>

    <?php endif; ?>

    <!-- DATA -->
    <?= $form->field($model, 'data')->input('date', [
        'value' => $model->data ?? date('Y-m-d'),
        'min' => date('Y-m-d'),
        'name' => 'data',
        'id' => 'sessao-data',
        'disabled' => $temBilhetes,
    ]) ?>

    <!-- HORA INÍCIO -->
    <?= $form->field($model, 'hora_inicio')->input('time', [
        'name' => 'hora_inicio',
        'id' => 'sessao-hora_inicio',
        'disabled' => $temBilhetes,
    ]) ?>

    <!-- FILME -->
    <?= $form->field($model, 'filme_id')->dropDownList($filmesEmExibicao,
        [
            'prompt' => 'Selecione o filme',
            'onchange' => 'this.form.submit()',
            'name' => 'filme_id',
            'disabled' => $temBilhetes,
        ]
    ) ?>

    <?php ActiveForm::end(); ?>

    <!-- FORM POST PARA GUARDAR -->
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'hora_fim')->input('time', ['value' => $model->hora_fim, 'disabled' => $temBilhetes,]) ?>

    <!-- CAMPOS OCULTOS QUE VÊM DO FORM GET -->
    <?= $form->field($model, 'cinema_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'filme_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'hora_inicio')->hiddenInput()->label(false) ?>

    <!-- SALAS DISPONÍVEIS -->
    <?php if ($model->cinema_id): ?>
        <?= $form->field($model, 'sala_id')->dropDownList($salasDropdown, ['prompt' => 'Selecione a sala']) ?>
    <?php endif; ?>

    <!-- BOTÃO GUARDAR -->
    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS
$(function() {
    
    var timer;
    
    // QUANDO DATA OU HORA INÍCIO MUDAR --> DAR UM DELAY ANTES DE RECARREGAR
    $('#sessao-data, #sessao-hora_inicio').on('input change', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            $('#sessao-form-get').submit();
        }, 500);
    });
    
});
JS;
$this->registerJs($script);
?>