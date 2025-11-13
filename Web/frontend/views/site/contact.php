<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\ContactForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\captcha\Captcha;
use yii\helpers\Url;

$iconsPath = '@webroot/icons/';
$btnClasses = 'btn btn-dark fw-medium rounded-3 w-100 fs-15 py-2';

$this->title = 'Serviços';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact container">

    <div class="mb-4">
        <h4 class="page-title m-0">Formulário de Contacto</h4>
        <p class="text-muted">Envia-nos uma mensagem ou um pedido de aluguer de sala privada.</p>
    </div>

    <div class="row align-items-stretch">
        <div class="col-12 col-md-8 mb-5 mb-md-0">
            <?php $form = ActiveForm::begin(['id' => 'contact-form',
                'fieldConfig' => [
                    'options' => ['class' => 'mb-2'],
                ]
            ]); ?>

            <div class="row row-cols-md-2">
                <?= $form->field($model, 'name')->textInput(['placeholder' => 'John Smith',]) ?>
                <?= $form->field($model, 'email')->textInput(['placeholder' => 'john.smith@email.com',]) ?>
            </div>

            <?= $form->field($model, 'subject') ?>
            <?= $form->field($model, 'body', ['options' => ['class' => 'mb-3']])->textarea(['rows' => 6]) ?>

            <div class="form-group">
                <?= Html::submitButton('Enviar', ['class' => $btnClasses, 'name' => 'button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="col-12 col-md-4 d-flex flex-column">
            <div class="box-gray rounded-4 mb-3 flex-grow-1 d-flex flex-column justify-content-between">
                <div class="d-flex h-100 flex-column justify-content-center align-items-center text-center">
                    <?= file_get_contents(Yii::getAlias($iconsPath . 'calendar.svg')) ?>
                    <h5 class="fw-semibold mt-2 mb-1" style="font-size: 18px; color: var(--gray-800);">Aluga uma sala</h5>
                    <p class="text-muted mb-0">Celebra com amigos e familiares.</p>
                </div>
            </div>
            <a href="<?= Url::to(['/aluguer-sala/create']) ?>" class="<?= $btnClasses ?>"><?= $currentUser->isGuest ? 'Iniciar Sessão' : 'Fazer Pedido' ?></a>
        </div>
    </div>


</div>
