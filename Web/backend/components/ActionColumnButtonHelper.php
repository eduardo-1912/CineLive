<?php

namespace backend\components;

use yii\helpers\Html;

class ActionColumnButtonHelper
{
    // ATIVAR/DESATIVAR UTILIZADOR
    public static function userButtons()
    {
        return [
            'activate' => function ($url, $model) {
                if ($model->status == $model::STATUS_INACTIVE || $model->status == $model::STATUS_DELETED) {
                    return Html::a('<i class="fas fa-user-plus"></i>', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-sm',
                        'title' => 'Ativar Utilizador',
                        'data-confirm' => 'Tem a certeza que quer ativar este utilizador?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'deactivate' => function ($url, $model) {
                if ($model->status == $model::STATUS_ACTIVE) {
                    return Html::a('<i class="fas fa-user-minus"></i>', ['deactivate', 'id' => $model->id], [
                        'class' => 'btn btn-secondary btn-sm',
                        'title' => 'Desativar Utilizador',
                        'data-confirm' => 'Tem a certeza que quer desativar este utilizador?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'softDelete' => function ($url, $model, $key) {
                return Html::a('<i class="fas fa-ban"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Eliminar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer eliminar este utilizador?',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },
        ];
    }

    // ATIVAR/ENCERRAR CINEMA
    public static function cinemaButtons()
    {
        return [
            'activate' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ENCERRADO) {
                    return Html::a('<i class="fas fa-toggle-on"></i>', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-sm',
                        'title' => 'Ativar Cinema',
                        'data-confirm' => 'Tem a certeza que quer ativar este cinema?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'deactivate' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ATIVO) {
                    return Html::a('<i class="fas fa-toggle-off"></i>', ['deactivate', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'title' => 'Encerrar Cinema',
                        'data-confirm' => 'Tem a certeza que quer encerrar este cinema?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
        ];
    }

    // ATIVAR/ENCERRAR SALA DE CINEMA
    public static function salaButtons()
    {
        return [
            'activate' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ENCERRADA) {
                    return Html::a('<i class="fas fa-toggle-on"></i>', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-sm',
                        'title' => 'Ativar Sala',
                        'data-confirm' => 'Tem a certeza que quer ativar esta sala?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'deactivate' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ATIVA) {
                    return Html::a('<i class="fas fa-toggle-off"></i>', ['deactivate', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'title' => 'Encerrar Sala',
                        'data-confirm' => 'Tem a certeza que quer encerrar esta sala?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
        ];
    }

}