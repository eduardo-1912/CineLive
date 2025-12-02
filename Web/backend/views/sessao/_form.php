<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */
/* @var $gerirSessoes bool */
/* @var $cinemaOptions array */
/* @var $hasBilhetes bool */
/* @var $filmeOptions array */
/* @var $salaOptions array */

?>

<div class="sessao-form">

    <!-- Form GET (Obter cinema, data, filme e hora início) -->
    <?php $form = ActiveForm::begin(['method' => 'get',
        'action' => [$model->isNewRecord ? 'create' : 'update', 'id' => $model->id],
        'id' => 'sessao-form-get'
    ]); ?>

    <?php if ($gerirSessoes): ?>

        <?= $form->field($model, 'cinema_id')->dropDownList($cinemaOptions, [
            'prompt' => 'Selecione o cinema',
            'onchange' => 'this.form.submit()',
            'name' => 'cinema_id',
            'disabled' => !$model->isNewRecord,
        ]) ?>

    <?php else: ?>
        <?= Html::hiddenInput('cinema_id', $model->cinema_id) ?>
    <?php endif; ?>

    <?= $form->field($model, 'data')->input('date', [
        'value' => $model->data ?? date('Y-m-d'),
        'min' => date('Y-m-d'),
        'name' => 'data',
        'id' => 'sessao-data',
        'disabled' => $hasBilhetes,
    ]) ?>

    <?= $form->field($model, 'hora_inicio')->input('time', [
        'name' => 'hora_inicio',
        'id' => 'sessao-hora_inicio',
        'disabled' => $hasBilhetes,
    ]) ?>

    <?= $form->field($model, 'filme_id')->dropDownList($filmeOptions, [
            'prompt' => 'Selecione o filme',
            'onchange' => 'this.form.submit()',
            'name' => 'filme_id',
            'disabled' => $hasBilhetes,
        ]) ?>

    <?php ActiveForm::end(); ?>

    <!-- Form POST (Criar sessão) -->
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'hora_fim')->input('time', ['value' => $model->hora_fim, 'disabled' => $hasBilhetes,]) ?>

    <!-- Dados do GET -->
    <?= $form->field($model, 'cinema_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'filme_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'hora_inicio')->hiddenInput()->label(false) ?>

    <?php if ($model->cinema_id): ?>
        <?= $form->field($model, 'sala_id')->dropDownList($salaOptions, ['prompt' => 'Selecione a sala']) ?>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS
$(function() {
    
    var timer;
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