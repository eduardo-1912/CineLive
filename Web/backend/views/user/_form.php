<?php

use common\models\User;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $form yii\bootstrap4\ActiveForm */

?>

<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput([
        'maxlength' => true,
        'placeholder' => $model->isNewRecord
            ? ''
            : '(opcional)',
    ]) ?>
    <?= $form->field($profile, 'nome')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'telemovel')->Input('number', ['maxlength' => 9]) ?>
    <?= $form->field($model, 'email')->input('email') ?>

    <!-- SE FOR ADMIN -> PODE SELECIONAR CINEMA/ROLE -->
    <?php if ($gerirUtilizadores): ?>

        <!-- DROPDOWN DOS ROLES-->
        <?= $form->field($model, 'role')->dropDownList(User::optsRoles()) ?>

        <!-- DROPDOWN DOS CINEMAS (VÍSIVEL SE ROLE SELECIONADO == FUNCIONÁRIO/GERENTE) -->
        <div id="formFieldCinema" style="display:none;">
            <?= $form->field($profile, 'cinema_id')->dropDownList($cinemasOptions, ['prompt' => 'Selecione o cinema']) ?>
        </div>

        <!-- DROPDOWN DE ESTADO -->
        <?= $form->field($model, 'status')->dropDownList(User::optsStatus()) ?>

    <!-- SE FOR GERENTE SÓ PODE CRIAR FUNCIONÁRIOS PARA O SEU CINEMA -->
    <?php elseif ($gerirFuncionarios): ?>

        <!-- ROLE 'FUNCIONÁRIO', CINEMA DO GERENTE E ESTADO 'ATIVO' -->
        <?= Html::activeHiddenInput($model, 'role', ['value' => 'funcionario']) ?>
        <?= Html::activeHiddenInput($profile, 'cinema_id', ['value' => Yii::$app->user->identity->profile->cinema_id]) ?>
        <?= Html::activeHiddenInput($model, 'status', ['value' => User::STATUS_ACTIVE]) ?>

    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS

    // MOSTRAR/ESCONDER CAMPO DE CINEMA CONSOANTE O ROLE SELECIONADO
    function toggleCinemaField()
    {
        // OBTER VALOR DO CAMPO ROLE
        var role = $('#user-role').val();
        
        // SE O ROLE SELECIONADO FOR GERENTE/FUNCIONÁRIO --> MOSTRAR CAMPO CINEMA
        if (role === 'gerente' || role === 'funcionario') {
            $('#formFieldCinema').show();
            $('#formFieldCinema select').prop('disabled', false);
        }
        
        // CASO CONTRÁRIO --> ESCONDER CAMPO CINEMA
        else {
            $('#formFieldCinema').hide();
            $('#formFieldCinema select').prop('disabled', true);
        }
    }
    
    $(document).ready(function()
    {
        toggleCinemaField();
        $('#user-role').on('change', toggleCinemaField);
    });

JS;
$this->registerJs($script);
?>
