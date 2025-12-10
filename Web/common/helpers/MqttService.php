<?php

namespace common\helpers;

use common\mosquitto\phpMQTT;
use Yii;

class MqttService
{
    private static string $server = '127.0.0.1';
    private static int $port = 1883;

    /**
     * Publicar mensagem num tópico
     *
     * @param string $topic
     * @param string $message
     * @return bool
     */
    public static function publish(string $topic, string $message): bool
    {
        // Atribuir um id único
        $clientId = 'cinelive_publisher_' . uniqid();

        // Criar cliente MQTT
        $mqtt = new phpMQTT(self::$server, self::$port, $clientId);

        // Tentar conectar
        if (!$mqtt->connect()) {
            Yii::error("MQTT ERROR: Unable to connect to broker at " . self::$server);
            return false;
        }

        // Publicar mensagem no tópico escolhido
        $mqtt->publish($topic, $message, 0);

        // Fechar ligação
        $mqtt->close();

        return true;
    }
}