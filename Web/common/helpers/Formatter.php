<?php

namespace common\helpers;

use Yii;
use yii\helpers\Html;

class Formatter
{
    public static function data($valor): ?string
    {
        $format = Yii::$app->params['dateFormat'];
        return Yii::$app->formatter->asDate($valor, $format);
    }

    public static function hora($valor): ?string
    {
        $format = Yii::$app->params['timeFormat'];
        return Yii::$app->formatter->asTime($valor, $format);
    }

    public static function horario($inicio, $fim): ?string
    {
        $format = Yii::$app->params['timeFormat'];

        $inicio = Yii::$app->formatter->asTime($inicio, $format);
        $fim = Yii::$app->formatter->asTime($fim, $format);

        return "{$inicio} - {$fim}";
    }

    public static function preco($valor): string
    {
        $format = Yii::$app->params['currency'];
        return number_format($valor, 2) . $format;
    }

    public static function minutos($minutos): string
    {
        return $minutos . ' min';
    }

    public static function horas($minutos): string
    {
        $horas = intdiv($minutos, 60);
        $restoMinutos = $minutos % 60;
        return "{$horas}h {$restoMinutos}min";
    }
}