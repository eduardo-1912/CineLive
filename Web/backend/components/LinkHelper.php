<?php

namespace backend\components;

use yii\helpers\Html;

class LinkHelper
{
    public static function filme($model)
    {
        return Html::a(
            $model->filme->titulo,
            ['filme/view', 'id' => $model->filme_id],
            ['class' => 'text-decoration-none text-primary']
        );
    }

    public static function cinema($model)
    {
        return Html::a(
            $model->cinema->nome,
            ['cinema/view', 'id' => $model->cinema_id],
            ['class' => 'text-decoration-none text-primary']
        );
    }

    public static function sala($model)
    {
        return Html::a(
            $model->sala->nome,
            ['sala/view', 'id' => $model->sala_id],
            ['class' => 'text-decoration-none text-primary']
        );
    }

    public static function sessao($model)
    {
        return Html::a(
            $model->sessao->nome,
            ['sessao/view', 'id' => $model->sessao_id],
            ['class' => 'text-decoration-none text-primary']
        );
    }


    public static function cliente($model)
    {
        return $model->cliente && $model->cliente->profile
            ? Html::a($model->cliente->profile->nome,
                ['user/view', 'id' => $model->cliente->id],
                ['class' => 'text-decoration-none text-primary'])
            : '<span class="text-muted">Conta eliminada</span>';
    }
}