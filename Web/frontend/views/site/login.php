<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="site-login container">

    <div class="d-flex justify-content-center align-items-center" style="min-height: 65vh;">
        <div class="w-100" style="max-width: 28rem;">

            <div class="mb-4">
                <h5 class="fw-semibold mb-0">Entre na sua conta</h5>
                <p class="text-muted">Preencha os seguintes campos para iniciar sessão.</p>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput() ?>

            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="loginform-password">Password</label>
                <a href="<?= Url::to(['site/request-password-reset']) ?>" class="fs-14 text-muted text-decoration-none">
                    Esqueceu-se da password?
                </a>
            </div>

            <?= $form->field($model, 'password')->passwordInput()->label(false) ?>

            <?= $form->field($model, 'rememberMe')->label('Lembrar-me')->checkbox() ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('Iniciar Sessão',
                    ['class' => 'btn btn-dark w-100', 'name' => 'login-button']
                ) ?>
            </div>

            <div class="mt-3 text-center fs-15 text-muted">
                Ainda não tem conta? <a href="<?= Url::to(['site/signup']) ?>" class="fs-15">Criar Conta</a>
            </div>

            <?php ActiveForm::end(); ?>


        </div>
    </div>
</div>
