<?php

use common\models\Cinema;
use common\models\Filme;
use common\models\Sala;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Json;

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

$temBilhetes = !$model->isNewRecord && count($model->lugaresOcupados) > 0;

?>

<div class="sessao-form">
    <?php $form = ActiveForm::begin(); ?>

    <!-- DATA -->
    <?= $form->field($model, 'data')->input('date', ['value' => $model->data,
        'min' => date('Y-m-d'), 'disabled' => $temBilhetes,]); ?>

    <!-- LISTA DAS DURAÇÕES DE TODOS OS FILMES (CONVERTER PARA JSON PARA USAR COM JAVASCRIPT) -->
    <?php $duracoesFilmes = Json::encode(ArrayHelper::map(Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])->all(), 'id', 'duracao')); ?>

    <!-- DROPDOWN DOS FILMES -->
    <?= $form->field($model, 'filme_id')->dropDownList(
        ArrayHelper::map(Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])
        ->orderBy('titulo')->all(), 'id', 'titulo'), ['prompt' => 'Selecione o filme', 'disabled' => $temBilhetes,]) ?>

    <!-- HORA INÍCIO -->
    <?= $form->field($model, 'hora_inicio')->input('time', ['disabled' => $temBilhetes]) ?>

    <!-- HORA FIM (CALCULADA CONSOANTE O FILME SELECIONADO) -->
    <?= $form->field($model, 'hora_fim')->input('time', ['disabled' => $temBilhetes]) ?>

    <!-- SE É ADMIN PODE ESCOLHER O CINEMA -->
    <?php if ($isAdmin): ?>
        <!-- DROPDOWN DE CINEMAS -->
        <?= $form->field($model, 'cinema_id')->dropDownList(
            ArrayHelper::map(Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])->orderBy('nome')->all(), 'id', 'nome'),
            ['prompt' => 'Selecione o cinema', 'onchange' => 'this.form.submit()', 'disabled' => !$model->isNewRecord]) ?>

        <!-- DROPDOWN DE SALAS -->
        <div id="formFieldSala" style="<?= $model->cinema_id ? '' : 'display: none;' ?>">
            <?= $form->field($model, 'sala_id')->dropDownList(
                ArrayHelper::map(Sala::find()->where(['estado' => Sala::ESTADO_ATIVA, 'cinema_id' => $model->cinema_id ?: null,])
                ->orderBy('numero')->all(), 'id', 'nome'), ['prompt' => 'Selecione a sala']) ?>
        </div>

    <!-- SE É GERENTE APENAS MOSTRAR SALAS DO SEU CINEMA -->
    <?php else: ?>
        <?= Html::activeHiddenInput($model, 'cinema_id', ['value' => $userCinemaId]) ?>

        <!-- DROPDOWN DE SALAS -->
        <?= $form->field($model, 'sala_id')->dropDownList(
            ArrayHelper::map(Sala::find()->where(['cinema_id' => $userCinemaId, 'estado' => Sala::ESTADO_ATIVA])
            ->orderBy('numero')->all(), 'id', 'numero'), ['prompt' => 'Selecione a sala']) ?>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS
$(function() {
    
    // FUNÇÃO PARA CALCULAR A HORA FIM CONSOANTE O FILME SELECIONADO E HORA DE INÍCIO
    function atualizarHoraFim() {
        
        // OBTER ARRAY DE DURAÇÕES DOS FILMES
        var duracoesFilmes = $duracoesFilmes;
    
        // OBTER FILME SELECIONADO E HORA INÍCIO
        var filmeId = $('#sessao-filme_id').val();
        var horaInicio = $('#sessao-hora_inicio').val();
        
        // OBTER DURAÇÃO DO FILME SELECIONADO
        var duracao = duracoesFilmes[filmeId];
    
        // SE FILME/INÍCIO/DURAÇÃO FOR NULL --> VOLTAR
        if (!filmeId || !horaInicio || !duracao) return;
    
        // TRANSFORMAR horaInicio PARA DATE OBJECT
        var [h, m] = horaInicio.split(':');
        var inicio = new Date();
        inicio.setHours(h);
        inicio.setMinutes(m);
    
        // SOMAR A DURAÇÃO DO FILME
        inicio.setMinutes(inicio.getMinutes() + parseInt(duracao));
    
        // FORMATAR HORA FIM PARA HH:mm
        var fimFormatado = inicio.toTimeString().slice(0, 5);
        $('#sessao-hora_fim').val(fimFormatado);
    }

    // FUNÇÃO PARA MOSTRAR O CAMPO SALA SE ALGUM CINEMA ESTIER SELECIONADO
    function toggleSalaField() {
        // OBTER O ID DO CINEMA SELECIONADO
        var cinemaId = $('#sessao-cinema_id').val();
        
        // SE ESTIVER ALGUM CINEMA SELECIONADO --> MOSTRAR CAMPO SALA
        if (cinemaId) {
            $('#formFieldSala').show();
            $('#formFieldSala select').prop('disabled', false);
        }
        
       // CASO CONTRÁRIO --> ESCONDER CAMPO SALA
        else {
            $('#formFieldSala').hide();
            $('#formFieldSala select').prop('disabled', true);
        }
    }
    
    $(document).ready(function() {
        // QUANDO O DOM ESTÁ PRONTO --> CHAMAR AS FUNÇÕES
        toggleSalaField();
        atualizarHoraFim();
        
        // SEMPRE QUE O USER MUDA O VALOR DO CAMPO 'CINEMA' --> MOSTRAR/ESCONDER CAMPO SALA
        $('#sessao-cinema_id').on('change', toggleSalaField);
        
        // QUANDO O USER SELECIONAR FILME E HORA INÍCIO --> CALCULAR HORA FIM
        $('#sessao-filme_id, #sessao-hora_inicio').on('change', atualizarHoraFim);
    });

});
JS;
$this->registerJs($script);
?>