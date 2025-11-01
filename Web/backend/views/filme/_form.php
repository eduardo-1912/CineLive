<?php

use common\models\Filme;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;


/** @var yii\web\View $this */
/** @var common\models\Filme $model */
/** @var yii\widgets\ActiveForm $form */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="filme-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sinopse')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'duracao')->textInput()->label('Duração (em minutos)') ?>

    <?= $form->field($model, 'rating')->dropDownList(Filme::optsRating()) ?>

    <?= $form->field($model, 'estreia')->input('date') ?>

    <?= $form->field($model, 'idioma')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'realizacao')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'trailer_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'posterFile')->fileInput() ?>

    <?php if ($model->poster_path): ?>
        <div class="mb-2">
            <?= Html::img($model->getPosterUrl(), ['style' => 'max-width:150px; border-radius:8px']) ?>
        </div>
    <?php endif; ?>

    <?= $form->field($model, 'estado')->dropDownList([ 'brevemente' => 'Brevemente', 'em_exibicao' => 'Em exibição', 'terminado' => 'Terminado', ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
