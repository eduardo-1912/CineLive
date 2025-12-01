<?php

namespace backend\components;

use yii\helpers\Html;

class LinkHelper
{
    public static function simple($valor, $path, $id)
    {
        return Html::a($valor, [$path, 'id' => $id],
            ['class' => 'text-decoration-none text-primary']);
    }

    public static function condition($valor, $path, $id, $else)
    {
        return $valor ? Html::a($valor, [$path, 'id' => $id],
            ['class' => 'text-decoration-none text-primary'])
            : '<span class="text-muted">' . $else . '</span>';
    }
}