<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use Yii;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <h4 class="mb-3">Dados da Conta</h4>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email') ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'status')->dropDownList([
        10 => 'Ativo',
        9 => 'Inativo',
        0 => 'Eliminado',
    ]) ?>

    <?= $form->field($model, 'role')->dropDownList([
        'admin' => 'Administrador',
        'gerente' => 'Gerente',
        'funcionario' => 'Funcionário',
        'cliente' => 'Cliente',
    ], ['prompt' => 'Selecione o papel']) ?>

    <hr>

    <h4 class="mb-3">Dados do Perfil</h4>

    <?= $form->field($profile, 'nome')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'telemovel')->textInput(['maxlength' => true]) ?>
    <?= $form->field($profile, 'cinema_id')->dropDownList(
        ArrayHelper::map(\common\models\Cinema::find()->all(), 'id', 'nome'),
        ['prompt' => 'Selecione o cinema']
    ) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
