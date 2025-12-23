<?php
/**
 * @var frontend\models\ContactForm $model
 */

use yii\helpers\Html;
?>

<table width="100%" cellpadding="0" cellspacing="0"
       style="font-family: Arial, sans-serif; background-color:#f7f7f7; padding:20px;">
    <tr>
        <td style="max-width:600px; margin:0 auto; background:#ffffff; padding:20px; border-radius:8px;">

            <h2 style="color:#333; margin-top:0;">
                Novo pedido de contacto
            </h2>

            <p style="font-size:15px; color:#444;">
                Assunto: <?= Html::encode($model->subject) ?>
            </p>

            <h3 style="margin-top:25px; color:#222;">Dados do Utilizador</h3>

            <table cellpadding="5" cellspacing="0"
                   style="width:100%; font-size:14px; color:#333;">
                <tr>
                    <td width="30%"><strong>Nome:</strong></td>
                    <td><?= Html::encode($model->name) ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?= Html::encode($model->email) ?></td>
                </tr>
            </table>

            <h3 style="margin-top:25px; color:#222;">Mensagem</h3>

            <p style="font-size:14px; color:#444; line-height:1.5;">
                <?= nl2br(Html::encode($model->body)) ?>
            </p>

        </td>
    </tr>
</table>
