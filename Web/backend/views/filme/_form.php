<?php

use common\models\Filme;
use common\models\Genero;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\JqueryAsset;

/** @var yii\web\View $this */
/** @var common\models\Filme $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="filme-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sinopse')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'duracao')->textInput()->label('Duração (em minutos)') ?>

    <?= $form->field($model, 'generosSelecionados')->dropDownList(
        $generosOptions, ['multiple' => true, 'id' => 'generos-select',]
    ) ?>

    <?= $form->field($model, 'rating')->dropDownList(Filme::optsRating()) ?>

    <?= $form->field($model, 'estreia')->input('date') ?>

    <?= $form->field($model, 'idioma')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'realizacao')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'trailer_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->dropDownList($model::optsEstado()) ?>

    <?= $form->field($model, 'posterFile')->fileInput() ?>

    <?php if ($model->poster_path): ?>
        <div class="mb-2">
            <?= Html::img($model->getPosterUrl(), [
                'style' => 'max-width:200px; border-radius:8px'
            ]) ?>
        </div>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- IMPORTAR O SELECT2 PARA SELECIONAR VÁRIOS GÉNEROS -->
<?php
$this->registerCssFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', [
    'depends' => [JqueryAsset::class],
]);

$this->registerJs("
    $('#generos-select').select2({
        theme: 'bootstrap-5',
        allowClear: true,
    });
");
?>
