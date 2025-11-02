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

<?php
$script = <<<JS

    // FUNÇÃO PARA MOSTRAR/ESCONDER CAMPO DE CINEMA CONSOANTE O ROLE SELECIONADO
    function toggleCinemaField() {
    
        // OBTER VALOR DO CAMPO DE ROLE
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
    
    $(document).ready(function() {
        
        // QUANDO O DOM ESTÁ PRONTO --> CHAMAR A FUNÇÃO
        toggleCinemaField();
        
        // SEMPRE QUE O USER MUDA O VALOR DO CAMPO 'ROLE' --> CHAMAR A FUNÇÃO
        $('#user-role').on('change', toggleCinemaField);
    });

JS;
$this->registerJs($script);
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'nome')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'telemovel')->label('Telemóvel')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email') ?>

    <!-- SE USER ATUAL FOR ADMIN PODE EDITAR TUDO -->
    <?php if (Yii::$app->user->can('admin')): ?>

        <!-- DROPDOWN DOS ROLES-->
        <?= $form->field($model, 'role')->label('Função')->dropDownList([
            'cliente' => 'Cliente',
            'funcionario' => 'Funcionário',
            'gerente' => 'Gerente',
            'admin' => 'Administrador',
        ]) ?>

        <!-- LISTA DE CINEMAS-->
        <?php
            // OBTER CINEMAS ATIVOS
            $cinemasQuery = Cinema::find()->where(['estado' => Cinema::ESTADO_ATIVO]);

            // SE O UTILIZADOR A SER EDITADO PERTENÇA A UM CINEMA ENCERRADO --> INCLUIR TAMBÉM ESSE
            if ($profile->cinema_id) {
                $cinemasQuery->orWhere(['id' => $profile->cinema_id]);
            }

            // GERAR LISTA DE CINEMAS
            $cinemas = ArrayHelper::map($cinemasQuery->orderBy('nome')->all(), 'id', 'nome');
        ?>

        <!-- DROPDOWN DOS CINEMAS-->
        <div id="formFieldCinema" style="display:none;">
            <?= $form->field($profile, 'cinema_id')->label('Cinema')->dropDownList($cinemas, ['prompt' => 'Selecione o cinema']) ?>
        </div>

        <!-- DROPDOWN DE ESTADO DA CONTA -->
        <?= $form->field($model, 'status')->label('Estado')->dropDownList([
            User::STATUS_ACTIVE => 'Ativa',
            User::STATUS_INACTIVE => 'Inativa',
            User::STATUS_DELETED => 'Eliminada',
        ]) ?>

    <!-- SE FOR GERENTE NÃO PODE ALTERAR ROLE NEM CINEMA, SÓ PODE CRIAR FUNCIONÁRIO PARA O SEU CINEMA -->
    <?php elseif (Yii::$app->user->can('gerente')): ?>

        <!-- ROLE 'FUNCIONÁRIO', CINEMA DO GERENTE E ESTADO DA CONTA 'ATIVVA' -->
        <?= Html::activeHiddenInput($model, 'role', ['value' => 'funcionario']) ?>
        <?= Html::activeHiddenInput($profile, 'cinema_id', ['value' => Yii::$app->user->identity->profile->cinema_id]) ?>
        <?= Html::activeHiddenInput($model, 'status', ['value' => 10]) ?>

    <?php else: ?>

        <!-- ROLE E CINEMA EM MODO READ-ONLY PARA FUNCIONÁRIOS -->
        <?= $form->field($model, 'role')->textInput(['value' => $model->role ? ucfirst($model->role) : '', 'readonly' => true,])->label('Função') ?>
        <?= $form->field($profile, 'cinema_id')->label('Cinema')->dropDownList(
                ArrayHelper::map(Cinema::find()->all(), 'id', 'nome'),
                ['disabled' => true]
            ) ?>

    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
