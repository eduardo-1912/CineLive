<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Cinema;

/* @var $this yii\web\View */
/* @var $model common\models\Sala */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $proximoNumero int|null */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$gerirSalas = $currentUser->can('gerirSalas');
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

$proximoNumero = $proximoNumero ?? null;

?>

<div class="sala-form">

    <!-- FORM GET PARA ESCOLHER O CINEMA -->
    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['create']]); ?>

    <!-- SE É ADMIN -> PODE ESCOLHER O CINEMA -->
    <?php if ($isAdmin): ?>
    
        <div class="form-group">
            <label for="cinema_id">Cinema</label>
            <?= Html::dropDownList('cinema_id', $model->cinema_id, $cinemasOptions, [
                'prompt' => 'Selecione o cinema',
                'class' => 'form-control',
                'onchange' => 'this.form.submit()',
                'disabled' => !$model->isNewRecord,
            ]) ?>
        </div>

    <?php elseif ($gerirSalas): ?>
        <?= Html::activeHiddenInput($model, 'cinema_id', ['value' => $userCinemaId]) ?>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>


    <!-- FORM DE CRIAÇÃO DE SALA -->
    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->cinema_id): ?>
        <?= $form->field($model, 'cinema_id')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <?php if ($proximoNumero != null && $model->isNewRecord): ?>
        <?= $form->field($model, 'numero')->textInput(['value' => $proximoNumero]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'num_filas')->textInput(['disabled' => !$model->isClosable()]) ?>
    <?= $form->field($model, 'num_colunas')->textInput(['disabled' => !$model->isClosable()]) ?>
    <?= $form->field($model, 'preco_bilhete')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'estado')->dropDownList($model::optsEstado(), ['disabled' => !$model->isClosable()]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
