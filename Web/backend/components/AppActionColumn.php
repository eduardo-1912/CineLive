<?php

namespace backend\components;

use common\models\User;
use yii\grid\ActionColumn;
use yii\helpers\Html;

class AppActionColumn extends ActionColumn
{
    public $header = 'Ações';
    public $headerOptions = ['class' => 'text-start', 'style' => 'width:120px;'];
    public $contentOptions = ['class' => 'text-center align-middle'];
    public $template = '{view} {update} {delete}';

    public function init()
    {
        parent::init();

        $this->buttons = array_merge($this->buttons, [
            'view' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-eye"></i>', $url, [
                    'class' => 'btn btn-sm btn-info',
                    'title' => 'Ver detalhes',
                    'data-toggle' => 'tooltip',
                ]);
            },
            'update' => function ($url, $model) {
                return Html::a('<i class="fas fa-edit"></i>', $url, [
                    'class' => 'btn btn-sm ' . ($model->isEditable() ? 'btn-warning' : ' btn-secondary disabled'),
                    'title' => 'Editar',
                ]);
            },
            'delete' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-trash"></i>', $url, [
                    'class' => 'btn btn-sm ' . ($model->isDeletable() ? 'btn-danger' : ' btn-secondary disabled'),
                    'title' => 'Eliminar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer eliminar permanentemente este registo?',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },
        ]);
    }

}
