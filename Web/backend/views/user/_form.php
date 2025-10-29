<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<?php $script = <<<JS

    // Mostrar Campo de Cinema em função do Role (Gerente/Funcionário)
    function toggleCinemaField() {
        var role = $('#userextension-role').val();
        
        if (role === 'gerente' || role === 'funcionario') {
            $('#formFieldCinema').show();
            $('#formFieldCinema select').prop('disabled', false);
        } else {
            $('#formFieldCinema').hide();
            $('#formFieldCinema select').prop('disabled', true);
        }
    }
    
    $(document).ready(function() {
        toggleCinemaField();
        $('#userextension-role').on('change', toggleCinemaField);
    });

JS; $this->registerJs($script); ?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <h4 class="mb-3">Dados do Utilizador</h4>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'nome')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'telemovel')->label('Telemóvel')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'role')->dropDownList([
            'cliente' => 'Cliente',
            'funcionario' => 'Funcionário',
            'gerente' => 'Gerente',
            'admin' => 'Administrador',
        ],
    ); ?>

    <div id="formFieldCinema" style="display: none;">
        <?= $form->field($profile, 'cinema_id')->label('Cinema')->dropDownList(
            ArrayHelper::map(Cinema::find()->all(), 'id', 'nome'),
            ['prompt' => 'Selecione o cinema'],
        ) ?>
    </div>

    <?= $form->field($model, 'status')->label('Estado da Conta')->dropDownList([
        10 => 'Ativa',
        9 => 'Inativa',
        0 => 'Eliminada',
    ]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
