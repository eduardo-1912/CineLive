<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var common\models\User $model */
/** @var bool $edit */
/** @var yii\web\View $this */

?>

<div>
    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'options' => ['class' => 'mb-2'],
        ]
    ]); ?>

    <div class="mb-4">
        <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'username')->textInput(['disabled' => !$edit]) ?>
        <?= $form->field($model, 'email')->textInput(['disabled' => !$edit]) ?>
        <?= $form->field($model->profile, 'nome')->textInput(['disabled' => !$edit]) ?>
        <?= $form->field($model->profile, 'telemovel')->Input('number', ['maxlength' => true, 'disabled' => !$edit]) ?>
        <?= $edit ? $form->field($model, 'password')->passwordInput(['placeholder' => '(opcional)']) : '' ?>
    </div>

    <div class="d-flex gap-2">
        <?php if ($edit): ?>
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success rounded-3 w-100']) ?>
            <?= Html::a('Cancelar', ['index'],
                ['class' => 'btn btn-light rounded-3 w-100',
                    'style' => 'background-color: var(--gray-200);']
            ) ?>
        <?php else: ?>
            <?= Html::a('Editar', ['index', 'edit' => 1],
                ['class' => 'btn btn-light rounded-3 w-100',
                    'style' => 'background-color: var(--gray-200);']
            ) ?>
            <a href="<?= Url::to(['/site/logout']) ?>" data-method="post" class="btn btn-danger rounded-3 w-100">Logout</a>
        <?php endif; ?>
    </div>

    <?php $form = ActiveForm::end(); ?>

    <?php if ($edit): ?>
        <div class="mt-2">
            <?= Html::a('Eliminar Conta', ['delete'], [
                'class' => 'btn btn-danger rounded-3 w-100',
                'data-method' => 'post',
                'data-confirm' => 'Tem a certeza que deseja eliminar a sua conta? Esta ação é irreversível.',
            ]) ?>
        </div>
    <?php endif; ?>
</div>
