<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login container">

    <div class="d-flex justify-content-center align-items-center" style="min-height: 65vh;">
        <div class="w-100" style="max-width: 420px;">

            <div class="mb-4">
                <h5 class="fw-semibold mb-0">Entre na sua conta</h5>
                <p class="text-muted">Preencha os seguintes campos para iniciar sessão.</p>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password', [
                'template' => '
                    <div class="d-flex justify-content-between">
                        <label class="form-label mb-0">{label}</label>
                        <a href="' . Url::to(['site/request-password-reset']) . '" class="fs-14 text-decoration-none">
                            Esqueceu-se da password?
                        </a>
                    </div>
                    {input}
                    {error}
                '
            ])->passwordInput() ?>
            <?= $form->field($model, 'rememberMe')->label('Lembrar-me')->checkbox() ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('Iniciar Sessão', ['class' => 'btn btn-dark w-100', 'name' => 'login-button']) ?>
            </div>

            <div class="mt-3 text-center" style="color:#999;">
                Ainda não tem conta? <?= Html::a('Criar Conta', ['site/signup']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
