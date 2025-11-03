<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\SessaoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row mt-2">
    <div class="col-md-12">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'data') ?>

    <?= $form->field($model, 'hora_inicio') ?>

    <?= $form->field($model, 'hora_fim') ?>

    <?= $form->field($model, 'filme_id') ?>

    <?php // echo $form->field($model, 'sala_id') ?>

    <?php // echo $form->field($model, 'cinema_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    </div>
    <!--.col-md-12-->
</div>
