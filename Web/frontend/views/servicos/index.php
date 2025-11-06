<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin([
        'id' => 'contact-form',
        'action' => ['servicos/contact'],
        'method' => 'post'
]); ?>

<div class="container my-5">
    <div class="row min-vh-50">
        <div class="col-md-8">
            <h4 class="fw-bold">Formulário de Contacto</h4>
            <p class="text-muted mb-4">
                Envia-nos uma mensagem ou um pedido de aluguer de sala privada.
            </p>

            <?= $form->field($model, 'nome') ?>

            <?= $form->field($model, 'email') ?>

            <?= $form->field($model, 'mensagem')->textarea(['rows' => 5]) ?>

            <div class="form-group">
                <?= Html::submitButton('Submit', ['class' => 'btn btn-dark w-100', 'name' => 'contact-button']) ?>
            </div>
        </div>

        <div class="col-md-4 d-flex flex-column justify-content-end">
            <div class="w-100 bg-light rounded-4 text-center py-5 shadow-sm mb-3">
                <div class="mb-3">
                    <!-- Ícone de calendário em SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="text-secondary" viewBox="0 0 16 16">
                        <path d="M14 3h-1V1h-1v2H4V1H3v2H2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM2 14V5h12v9H2z"/>
                    </svg>
                </div>
                <h5 class="fw-bold mb-1">Aluga uma sala</h5>
                <p class="text-muted mb-0">Celebra com familiares e amigos.</p>
            </div>

            <?php if (Yii::$app->user->isGuest): ?>
                <a href="<?= \yii\helpers\Url::to(['site/login']) ?>" class="btn btn-dark w-100">
                    Iniciar Sessão
                </a>
            <?php else: ?>
                <a href="<?= \yii\helpers\Url::to(['aluguer-sala/index']) ?>" class="btn btn-dark w-100">
                    Alugar uma Sala
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>
