<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/* @var yii\web\View $this */
/* @var common\models\AluguerSala $model */
/* @var array $cinemaOptions */
/* @var array $salaOptions */

?>

<div>

    <!-- Form GET -->
    <?php $formGet = ActiveForm::begin([
        'method' => 'get', 'action' => ['aluguer-sala/create'],
        'id' => 'aluguersala-form-get'
    ]); ?>

    <div class="row row-cols-sm-3">
        <?= $formGet->field($model, 'data')->input('date', [
            'value' => $model->data, 'min' => date('Y-m-d'), 'name' => 'data',
        ]) ?>
        <?= $formGet->field($model, 'hora_inicio')->input('time', ['name' => 'hora_inicio',]) ?>
        <?= $formGet->field($model, 'hora_fim')->input('time', ['name' => 'hora_fim',]) ?>
    </div>

    <?= $formGet->field($model, 'cinema_id')->dropDownList(
        $cinemaOptions, ['prompt' => 'Selecione o cinema',
        'name' => 'cinema_id', 'onchange' => 'this.form.submit()']
    ) ?>

    <?php ActiveForm::end(); ?>

    <!-- Form POST -->
    <?php $formPost = ActiveForm::begin(['method' => 'post']); ?>

    <!-- Campos ocultos do GET -->
    <?= $formPost->field($model, 'data')->hiddenInput()->label(false) ?>
    <?= $formPost->field($model, 'hora_inicio')->hiddenInput()->label(false) ?>
    <?= $formPost->field($model, 'hora_fim')->hiddenInput()->label(false) ?>
    <?= $formPost->field($model, 'cinema_id')->hiddenInput()->label(false) ?>

    <?= $formGet->field($model, 'sala_id')->dropDownList(
            $salaOptions, ['prompt' => 'Selecione a sala']
    ) ?>

    <?= $formPost->field($model, 'tipo_evento') ?>
    <?= $formPost->field($model, 'observacoes')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Enviar Pedido', ['class' => 'btn btn-dark fw-medium rounded-3 py-2 w-100']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS

    $(function() {
        var timer;
        
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