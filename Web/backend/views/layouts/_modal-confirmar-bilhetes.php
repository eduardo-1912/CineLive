<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
?>

<div class="modal fade" id="modal-confirmar-bilhetes" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarLabel">Confirmar Bilhetes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin(['id' => 'form-confirmar-bilhetes', 'action' => Url::to(['/bilhetes/validate']), 'method' => 'post',]); ?>

                <div class="form-group">
                    <?= Html::label('Código do Bilhete', 'codigo-bilhetes') ?>
                    <?= Html::textInput('codigo', '', ['id' => 'codigo-bilhetes', 'class' => 'form-control',
                        'required' => true, 'placeholder' => 'Ex: ABC123',]) ?>
                </div>

                <div class="form-group form-check ms-1">
                    <?= Html::checkbox('confirmar_todos', false, ['class' => 'form-check-input', 'id' => 'confirmar-todos', 'value' => 1,]) ?>
                    <?= Html::label('Confirmar todos os bilhetes pendentes da mesma compra', 'confirmar-todos', ['class' => 'form-check-label']) ?>
                </div>

                <?= Html::submitButton('Confirmar Bilhete', ['class' => 'btn btn-success btn-block',]) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>