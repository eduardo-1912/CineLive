<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;
use common\models\Sala;
use common\models\Filme;

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

$temBilhetes = !$model->isNewRecord && count($model->lugaresOcupados) > 0;

?>

<div class="sessao-form">

    <!-- FORM GET PARA OBTER DADOS -->
    <?php $form = ActiveForm::begin(['method' => 'get',
        'action' => [$model->isNewRecord ? 'create' : 'update', 'id' => $model->id],
        'id' => 'sessao-form-get'
    ]); ?>

    <?php if ($isAdmin): ?>

        <!-- DROPDOWN DE CINEMAS -->
        <?= $form->field($model, 'cinema_id')->dropDownList(
            ArrayHelper::map(Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO])
                ->orderBy('nome')->all(), 'id', 'nome'),
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
    <?= $form->field($model, 'filme_id')->dropDownList(
        ArrayHelper::map(Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])
            ->orderBy('titulo')->all(), 'id', 'titulo'),
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

    <!-- HORA FIM -->
    <?php

    // SE FILME E HORA INÍCIO JÁ TÊM VALOR --> CALCULAR HORA FIM
    if ($model->filme_id && $model->hora_inicio) {
        $filme = Filme::findOne($model->filme_id);
        if ($filme) {
            $model->hora_fim = $model->getHoraFimCalculada($filme->duracao);
        }
    }

    ?>

    <?= $form->field($model, 'hora_fim')->input('time', [
        'value' => $model->hora_fim,
    ]) ?>

    <!-- CAMPOS OCULTOS QUE VÊM DO FORM GET -->
    <?= $form->field($model, 'cinema_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'filme_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'hora_inicio')->hiddenInput()->label(false) ?>

    <!-- SALAS DISPONÍVEIS -->
    <?php if ($model->cinema_id): ?>
        <?php
        $salas = [];

        if ($model->data && $model->hora_inicio && $model->hora_fim) {
            $salas = Sala::getSalasDisponiveis(
                $model->cinema_id,
                $model->data,
                $model->hora_inicio,
                $model->hora_fim
            );
        }
        else {
            $salas = Sala::find()
                ->where(['cinema_id' => $model->cinema_id, 'estado' => Sala::ESTADO_ATIVA])
                ->orderBy('numero')
                ->all();
        }

        // SE FOR UPDATE --> INCLUIR A SALA ATUAL
        if ($model->sala_id) {
            $salaAtual = Sala::findOne($model->sala_id);
            if ($salaAtual && !in_array($salaAtual, $salas, true)) {
                $salas[] = $salaAtual;
            }
        }

        // ORDENAR AS SALAS POR NÚMERO
        usort($salas, fn($a, $b) => $a->numero <=> $b->numero);

        ?>
        <?= $form->field($model, 'sala_id')->dropDownList(
            ArrayHelper::map($salas, 'id', 'numero'),
            ['prompt' => 'Selecione a sala']
        ) ?>
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
        }, 600);
    });
});
JS;
$this->registerJs($script);
?>
