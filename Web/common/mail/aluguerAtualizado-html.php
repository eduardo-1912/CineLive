<?php
/** @var common\models\AluguerSala $aluguer */
/** @var string $mensagem */

use common\helpers\Formatter;

?>
<table width="100%" cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; background-color:#f7f7f7; padding:20px;">
    <tr>
        <td style="max-width:600px; margin:0 auto; background:#ffffff; padding:20px; border-radius:8px;">

            <h2 style="color:#333; margin-top:0;">Atualização do seu pedido de aluguer</h2>

            <p style="font-size:15px; color:#444;">
                <?= nl2br($mensagem) ?>
            </p>

            <h3 style="margin-top:25px; color:#222;">Detalhes do Aluguer</h3>
            <table cellpadding="5" cellspacing="0" style="width:100%; font-size:14px; color:#333;">
                <tr>
                    <td><strong>ID do Aluguer:</strong></td>
                    <td>#<?= $aluguer->id ?></td>
                </tr>
                <tr>
                    <td><strong>Cinema:</strong></td>
                    <td><?= $aluguer->cinema->nome ?></td>
                </tr>
                <tr>
                    <td><strong>Sala:</strong></td>
                    <td><?= $aluguer->sala->nome ?></td>
                </tr>
                <tr>
                    <td><strong>Data:</strong></td>
                    <td><?= Formatter::data($aluguer->data) ?></td>
                </tr>
                <tr>
                    <td><strong>Horário:</strong></td>
                    <td><?= $aluguer->horario ?></td>
                </tr>
                <tr>
                    <td><strong>Tipo de Evento:</strong></td>
                    <td><?= $aluguer->tipo_evento ?></td>
                </tr>
                <?php if (!empty($aluguer->observacoes)) : ?>
                    <tr>
                        <td><strong>Observações:</strong></td>
                        <td><?= nl2br($aluguer->observacoes) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <p style="font-size:13px; color:#777; margin-top:25px;">
                Obrigado por escolher o CineLive.
                <br>Estamos ao dispor para qualquer questão.
            </p>
        </td>
    </tr>
</table>
