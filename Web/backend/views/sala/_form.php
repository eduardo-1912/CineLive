<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $form yii\bootstrap4\ActiveForm */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirSalas = $currentUser->can('gerirSalas');
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

$isNewRecord = $model->isNewRecord ? 'true' : 'false';

// ARRAY COM PRÓXIMO NÚMERO DA SALA PARA CADA CINEMA
$arrayProximosNumeros = json_encode(Cinema::getProximoNumeroPorCinema());

?>

<div class="sala-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php if ($isAdmin): ?>
        <?php
            // OBTER CINEMAS ATIVOS
            $cinemasQuery = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO]);

            // SE A SALA A SER EDITADA PERTENÇE A UM CINEMA ENCERRADO --> INCLUIR ESSE CINEMA
            if ($model->cinema_id) {
                $cinemasQuery->orWhere(['id' => $model->cinema_id]);
            }

            // GERAR LISTA DE CINEMAS
            $cinemas = ArrayHelper::map($cinemasQuery->orderBy('nome')->all(), 'id', 'nome');
        ?>

        <?= $form->field($model, 'cinema_id')->dropDownList(
            $cinemas, ['prompt' => 'Selecione o cinema', 'disabled' => !$model->isNewRecord]) ?>

    <?php elseif ($gerirSalas): ?>
        <?= Html::activeHiddenInput($model, 'cinema_id', ['value' => $userCinemaId]) ?>
    <?php endif; ?>

    <div id="formFieldNumero" style="display: none;">
        <?= $form->field($model, 'numero')->textInput() ?>
    </div>

    <?= $form->field($model, 'num_filas')->textInput(['disabled' => !$model->isClosable()]) ?>
    <?= $form->field($model, 'num_colunas')->textInput(['disabled' => !$model->isClosable()]) ?>
    <?= $form->field($model, 'preco_bilhete')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->dropDownList($model::optsEstado(), ['disabled' => !$model->isClosable()]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$script = <<<JS

    // OBTER ARRAY COM PRÓXIMOS NÚMEROS DE SALA
    const proximosNumeros = $arrayProximosNumeros;
    const isNewRecord = $isNewRecord;

    // FUNÇÃO PARA MOSTRAR O CAMPO NÚMERO SE ALGUM CINEMA ESTIVER SELECIONADO
    function toggleNumeroField()
    {
        // OBTER O VALOR DO CAMPO CINEMA
        var cinemaId = $('#sala-cinema_id').val();
        
        // SE NENHUM CINEMA ESTIVER SELECIONADO --> ESCONDER CAMPO NÚMERO
        if (isNewRecord) {
            if (!cinemaId) {
                 $('#formFieldNumero').hide();
                $('#sala-numero').val('');
            }
            else {
                $('#formFieldNumero').show();
                var proximoNumero = proximosNumeros[cinemaId] || 1;
                $('#sala-numero').val(proximoNumero);
            }
        }
        else {
            $('#formFieldNumero').show();
        }
    }

    $(document).ready(function() {
        toggleNumeroField();
        $('#sala-cinema_id').on('change', toggleNumeroField);
    });
    
JS;

$this->registerJs($script);
?>