<?php

namespace backend\helpers;

use yii\helpers\Html;

class LinkHelper
{
    public static function simple($valor, $path, $id)
    {
        return Html::a($valor, [$path, 'id' => $id],
            ['class' => 'text-decoration-none text-primary']);
    }

    public static function nullSafe($valor, $path, $id, $null)
    {
        return $valor ? Html::a($valor, [$path, 'id' => $id],
            ['class' => 'text-decoration-none text-primary'])
            : '<span class="text-muted">' . $null . '</span>';
    }
}