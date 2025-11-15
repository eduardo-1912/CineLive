<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$iconsPath = '@webroot/icons/';
$btnClasses = 'btn btn-dark fw-medium rounded-3 w-100 fs-15 py-2';

$this->title = 'Alugar Sala';

?>

<div class="container">

    <div class="mb-4">
        <h4 class="page-title m-0">Aluga uma Sala</h4>
        <p class="text-muted">Envia-nos um pedido de aluguer de sala privada e um gerente entrará em contacto consigo.</p>
    </div>


    <div>

        <!-- FORM GET PARA MOSTRAR APENAS AS SALAS DISPONÍVEIS PARA O CINEMA E HORÁRIO SELECIONADO -->
        <?php $formGet = ActiveForm::begin([
            'method' => 'get', 'action' => ['aluguer-sala/create'],
            'id' => 'aluguersala-form-get'
        ]); ?>

        <div class="row row-cols-sm-3">
            <?= $formGet->field($model, 'data')->input('date', [
                'value' => $model->data ?: date('Y-m-d'),
                'min' => date('Y-m-d'), 'name' => 'data',
            ]) ?>
            <?= $formGet->field($model, 'hora_inicio')->input('time', ['name' => 'hora_inicio',]) ?>
            <?= $formGet->field($model, 'hora_fim')->input('time', ['name' => 'hora_fim',]) ?>
        </div>

        <?= $formGet->field($model, 'cinema_id')->dropDownList(
            $cinemasOptions, ['prompt' => 'Selecione o cinema', 'name' => 'cinema_id', 'onchange' => 'this.form.submit()']
        ) ?>

        <?php ActiveForm::end(); ?>

        <?php $form = ActiveForm::begin(['method' => 'post']); ?>

        <!-- CAMPOS OCULTOS (TRAZIDOS PELO GET) -->
        <?= $form->field($model, 'data')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'hora_inicio')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'hora_fim')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'cinema_id')->hiddenInput()->label(false) ?>

        <?= $formGet->field($model, 'sala_id')->dropDownList($salasOptions, ['prompt' => 'Selecione a sala',]) ?>

        <?= $form->field($model, 'tipo_evento') ?>
        <?= $form->field($model, 'observacoes')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton('Enviar Pedido', ['class' => 'btn btn-dark fw-medium rounded-3 py-2 w-100']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>

<?php
$script = <<<JS
$(function() {
    
    var timer;
    
    // QUANDO HORÁRIO MUDAR --> DAR UM DELAY ANTES DE RECARREGAR A PÁGINA
    $('#aluguersala-data, #aluguersala-hora_inicio, #aluguersala-hora_fim').on('input change', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            $('#aluguersala-form-get').submit();
        }, 400);
    });
    
});
JS;
$this->registerJs($script);
?>