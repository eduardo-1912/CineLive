<?php

use common\models\Cinema;
use common\models\Filme;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sessao */
/* @var $form yii\bootstrap4\ActiveForm */

$currentUser = Yii::$app->user;
$isAdmin = $currentUser->can('admin');
$isGerente = $currentUser->can('gerente');
$userCinemaId = $currentUser->identity->profile->cinema_id ?? null;

?>

<div class="sessao-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'data')->input('date') ?>

    <?= $form->field($model, 'hora_inicio')->input('time') ?>

    <?= $form->field($model, 'hora_fim')->input('time') ?>

    <?= $form->field($model, 'filme_id')->dropDownList(ArrayHelper::map(
        Filme::find()->where(['estado' => Filme::ESTADO_EM_EXIBICAO])
        ->orderBy('titulo')->all(), 'id', 'titulo'), ['prompt' => 'Selecione o filme']) ?>

    <?php if ($isAdmin): ?>
        <?= $form->field($model, 'cinema_id')->dropDownList(ArrayHelper::map(Cinema::find()->all(), 'id', 'nome')) ?>
    <?php elseif ($isGerente): ?>
        <?= Html::activeHiddenInput($model, 'cinema_id', ['value' => $userCinemaId]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'sala_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
