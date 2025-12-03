<?php

use common\models\User;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $gerirUtilizadores bool */
/* @var $isOwnAccount bool */
/* @var $criarFuncionariosCinema bool */
/* @var $cinemaOptions array */
/* @var $userCinemaId int|null */

?>

<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'placeholder' => $model->isNewRecord ? '' : '(opcional)',]) ?>
    <?= $form->field($profile, 'nome')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'telemovel')->textInput(['type' => 'tel', 'maxlength' => 9, 'pattern' => '[0-9]{9}']) ?>
    <?= $form->field($model, 'email')->input('email') ?>

    <?php if ($gerirUtilizadores): ?>

        <?= $form->field($model, 'role')->dropDownList(
            User::optsRoles(),
            ['disabled' => ($isOwnAccount || $model->roleName === 'Administrador')]
        ) ?>
        <div id="formFieldCinema" style="display:none;">
            <?= $form->field($profile, 'cinema_id')->dropDownList($cinemaOptions, ['prompt' => 'Selecione o cinema']) ?>
        </div>
        <?= $form->field($model, 'status')->dropDownList(User::optsStatus()) ?>

    <?php elseif ($userCinemaId): ?>

        <?= Html::activeHiddenInput($model, 'role', ['value' => 'funcionario']) ?>
        <?= Html::activeHiddenInput($profile, 'cinema_id', ['value' => $userCinemaId]) ?>
        <?= Html::activeHiddenInput($model, 'status', ['value' => $model::STATUS_ACTIVE]) ?>

    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS

    // Mostrar/esconder o campo de cinema se o role for gerente ou funcionário
    function toggleCinemaField() {
        var role = $('#user-role').val();
        
        if (role === 'gerente' || role === 'funcionario') {
            $('#formFieldCinema').show();
            $('#formFieldCinema select').prop('disabled', false);
        }
        else {
            $('#formFieldCinema').hide();
            $('#formFieldCinema select').prop('disabled', true);
        }
    }
    
    $(document).ready(function() {
        toggleCinemaField();
        $('#user-role').on('change', toggleCinemaField);
    });

JS;
$this->registerJs($script);
?>
