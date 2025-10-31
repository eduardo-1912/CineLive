<?php

namespace backend\components;

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

        $this->buttons = [

            // VER DETALHES
            'view' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-eye"></i>', $url, [
                    'class' => 'btn btn-sm btn-info',
                    'title' => 'Ver detalhes',
                    'data-toggle' => 'tooltip',
                ]);
            },

            // EDITAR/ATUALIZAR
            'update' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-edit"></i>', $url, [
                    'class' => 'btn btn-sm btn-warning',
                    'title' => 'Editar',
                    'data-toggle' => 'tooltip',
                ]);
            },

            // ELIMINAR
            'delete' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-trash"></i>', $url, [
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Eliminar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer eliminar permanentemente este registo?',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },

            // ATIVAR UTILIZADOR
            'activate' => function ($url, $model, $key) {
                if ($model->status == 9 || $model->status == 0) {
                    return Html::a('<i class="fas fa-user-plus"></i>', $url, [
                        'class' => 'btn btn-sm btn-success',
                        'title' => 'Ativar',
                        'data' => [
                            'confirm' => 'Tem a certeza que quer ativar este utilizador?',
                            'method' => 'post',
                        ],
                        'data-toggle' => 'tooltip',
                    ]);
                }
                return ''; // se ativo, não mostra
            },

            // DESATIVAR UTILIZADOR
            'deactivate' => function ($url, $model, $key) {
                if ($model->status == 10) { // apenas mostra se ativo
                    return Html::a('<i class="fas fa-user-minus"></i>', $url, [
                        'class' => 'btn btn-sm btn-secondary',
                        'title' => 'Desativar',
                        'data' => [
                            'confirm' => 'Tem a certeza que quer desativar este utilizador?',
                            'method' => 'post',
                        ],
                        'data-toggle' => 'tooltip',
                    ]);
                }
                return ''; // se inativo, não mostra
            },

            // ARQUIVAR
            'archive' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-archive"></i>', $url, [
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Arquivar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer arquivar este registo?',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },

            // DESARQUIVAR
            'unarchive' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-archive"></i>', $url, [
                    'class' => 'btn btn-sm btn-success',
                    'title' => 'Desarquivar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer desarquivar este registo?',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },
        ];
    }
}
