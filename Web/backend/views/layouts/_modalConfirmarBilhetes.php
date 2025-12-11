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

                <!-- QR Code Scanner -->
                <div id="qr-scanner" class="w-100 rounded-2 overflow-hidden"></div>

                <?php $form = ActiveForm::begin(['id' => 'form-confirmar-bilhetes', 'action' => Url::to(['/bilhete/validate']), 'method' => 'post',]); ?>
                <div class="form-group mt-2">
                    <?= Html::label('Código do Bilhete', 'codigo-bilhete') ?>
                    <?= Html::textInput('codigo', '', ['id' => 'codigo-bilhete', 'class' => 'form-control',
                        'required' => true, 'placeholder' => 'Ex: ABC123',]) ?>
                </div>
                <div class="form-group form-check ms-1">
                    <?= Html::checkbox('confirmar_todos', true, ['class' => 'form-check-input', 'id' => 'confirmar-todos', 'value' => 1]) ?>
                    <?= Html::label('Confirmar todos os bilhetes pendentes da mesma compra', 'confirmar-todos', ['class' => 'form-check-label']) ?>
                </div>
                <?= Html::submitButton('Confirmar Bilhete', ['class' => 'btn btn-success btn-block',]) ?>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var scanner = null;

        const modal = $('#modal-confirmar-bilhetes');
        const qrScannerPreview = $("#qr-scanner");
        const codigo = $("#codigo-bilhete");
        const form = $("#form-confirmar-bilhetes");

        modal.on('shown.bs.modal', function () {
            // Criar scanner e mostrar preview
            scanner = new Html5Qrcode("qr-scanner");
            qrScannerPreview.show();

            scanner.start(
                { facingMode: "environment" },
                {
                    fps: 15,
                    qrbox: 250
                },
                qrCodeMessage => {
                    // Colocar o valor no input de código
                    codigo.val(qrCodeMessage);

                    // Parar a câmara e esconder preview
                    scanner.stop().then(() => {
                        qrScannerPreview.hide();
                    });

                    // Submeter o form
                    form.submit();
                },
                errorMessage => {}
            ).catch(err => {
                qrScannerPreview.hide();
            });
        });

        // Desligar a câmara quando o modal fechar
        modal.on('hidden.bs.modal', function () {
            if (scanner) scanner.stop();
            qrScannerPreview.hide();
        });
    });
</script>
