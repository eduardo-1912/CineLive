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
$isGerente = $currentUser->can('gerente');
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

?>

<div class="sala-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'numero')->textInput() ?>
    <?= $form->field($model, 'num_filas')->textInput() ?>
    <?= $form->field($model, 'num_colunas')->textInput() ?>
    <?= $form->field($model, 'preco_bilhete')->textInput(['maxlength' => true]) ?>

    <?php if ($isAdmin): ?>
        <?= $form->field($model, 'cinema_id')->dropDownList(ArrayHelper::map(Cinema::find()->all(), 'id', 'nome')) ?>
    <?php elseif ($isGerente): ?>
        <?= Html::activeHiddenInput($model, 'cinema_id', ['value' => $userCinemaId]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'estado')->dropDownList([
        'ativa' => 'Ativa',
        'encerrada' => 'Encerrada'
    ]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
