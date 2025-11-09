<?php

namespace common\components;

use Throwable;
use Yii;

class EmailHelper
{
    public static function enviarEmail($to, $assunto, $mensagem)
    {
        if (empty($to)) {
            Yii::warning("Tentativa de envio de email sem destinatário.", __METHOD__);
            return false;
        }

        try {
            Yii::$app->mailer->compose()
                ->setFrom(['noreply@cinelive.pt' => 'CineLive'])
                ->setTo($to)
                ->setSubject($assunto)
                ->setHtmlBody($mensagem)
                ->send();

            Yii::info("Email enviado para {$to} ({$assunto})", __METHOD__);
            return true;

        } catch (Throwable $e) {
            Yii::error("Erro ao enviar email: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}