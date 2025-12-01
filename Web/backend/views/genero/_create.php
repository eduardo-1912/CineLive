<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/** @var $model common\models\Genero */
?>

<?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'post',]); ?>

<div class="d-flex align-items-start gap-1">
    <?= $form->field($model, 'nome')->textInput([
            'maxlength' => true, 'placeholder' => 'Ex.: Ação, Comédia'
    ])->label(false) ?>
    <?= Html::submitButton('Criar Género', ['class' => 'btn btn-success', 'style' => 'height: 38px']) ?>
</div>

<?php ActiveForm::end(); ?>
