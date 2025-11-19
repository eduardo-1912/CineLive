<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

$this->title = 'Criar Conta';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup container">

    <div class="d-flex justify-content-center align-items-center" style="min-height: 65vh;">
        <div class="w-100" style="max-width: 420px;">

            <div class="mb-4">
                <h5 class="fw-semibold mb-0">Crie um conta</h5>
                <p class="text-muted">Preencha os seguintes campos para se registar.</p>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'nome') ?>
            <?= $form->field($model, 'telemovel') ?>
            <?= $form->field($model, 'email') ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('Criar Conta', ['class' => 'btn btn-dark w-100', 'name' => 'signup-button']) ?>
            </div>

            <div class="mt-3 text-center fs-15" style="color:#999;">
                Já tem uma conta? <a href="<?= Url::to(['site/login']) ?>" class="fs-15">Iniciar Sessão</a>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>
