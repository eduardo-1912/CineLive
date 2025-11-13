<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Criar Conta';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Por favor, preencha os seguintes campos para se registar:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'nome') ?>
            <?= $form->field($model, 'telemovel') ?>
            <?= $form->field($model, 'email') ?>

            <div class="form-group">
                <?= Html::submitButton('Criar Conta', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>