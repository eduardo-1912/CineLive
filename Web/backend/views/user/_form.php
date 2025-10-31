<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;

/* @var $this yii\web\View */
/* @var $model common\models\UserExtension */
/* @var $profile common\models\UserProfile */
/* @var $form yii\bootstrap4\ActiveForm */

?>

<?php

// Script JS para mostrar o campo cinema (gerentes/funcionários)
$script = <<<JS
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
JS;
$this->registerJs($script);
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <h4 class="mb-3">Dados do Utilizador</h4>

    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'nome')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'telemovel')->label('Telemóvel')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email') ?>

    <?php if (Yii::$app->user->can('admin')): ?>

        <!-- ADMIN pode editar tudo -->
        <?= $form->field($model, 'role')->label('Função')->dropDownList([
            'cliente' => 'Cliente',
            'funcionario' => 'Funcionário',
            'gerente' => 'Gerente',
            'admin' => 'Administrador',
        ]) ?>

        <div id="formFieldCinema" style="display:none;">
            <?= $form->field($profile, 'cinema_id')
                ->label('Cinema')
                ->dropDownList(
                    ArrayHelper::map(Cinema::find()->all(), 'id', 'nome'),
                    ['prompt' => 'Selecione o cinema']
                ) ?>
        </div>

        <?= $form->field($model, 'status')->label('Estado')->dropDownList([
            10 => 'Ativa',
            9 => 'Inativa',
            0 => 'Eliminada',
        ]) ?>

    <?php elseif (Yii::$app->user->can('gerente')): ?>

        <!-- GERENTE: cria funcionário, role/cinema são automáticos -->
        <?= Html::activeHiddenInput($model, 'role', ['value' => 'funcionario']) ?>
        <?= Html::activeHiddenInput($profile, 'cinema_id', ['value' => Yii::$app->user->identity->profile->cinema_id]) ?>
        <?= Html::activeHiddenInput($model, 'status', ['value' => 10]) ?>

    <?php else: ?>

        <!-- OUTROS (funcionário/cliente): apenas leitura -->
        <?= $form->field($model, 'role')->textInput([
            'value' => $model->role ? ucfirst($model->role) : '',
            'readonly' => true,
        ])->label('Função') ?>

        <?= $form->field($profile, 'cinema_id')
            ->label('Cinema')
            ->dropDownList(
                ArrayHelper::map(Cinema::find()->all(), 'id', 'nome'),
                ['disabled' => true]
            ) ?>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
