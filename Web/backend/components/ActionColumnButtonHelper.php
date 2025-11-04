<?php

namespace backend\components;

use yii\helpers\Html;

class ActionColumnButtonHelper
{
    // USERS
    public static function userButtons()
    {
        return [
            'activate' => function ($url, $model) {
                if ($model->status == $model::STATUS_INACTIVE || $model->status == $model::STATUS_DELETED) {
                    $btnColor = $model->isStatusInactive() ? 'btn-success' : 'btn-primary';
                    return Html::a('<i class="fas fa-user-plus"></i>', ['activate', 'id' => $model->id], [
                        'class' => 'btn btn-sm ' . $btnColor,
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
            'softDelete' => function ($url, $model) {
                return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Eliminar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer eliminar este utilizador?',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },
            'hardDelete' => function ($url, $model) {
                return Html::a('<i class="fas fa-skull"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-danger',
                    'title' => 'Eliminar',
                    'data' => [
                        'confirm' => 'Tem a certeza que quer eliminar este utilizador permanentemente? Esta ação não pode ser desfeita!',
                        'method' => 'post',
                    ],
                    'data-toggle' => 'tooltip',
                ]);
            },
        ];
    }

    // CINEMAS
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

    // SALAS
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

    public static function sessaoButtons()
    {
        return [
            'hardDelete' => function ($url, $model) {
                if (count($model->lugaresOcupados) == 0) {
                    return Html::a('<i class="fas fa-skull"></i>', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'title' => 'Eliminar',
                        'data-confirm' => 'Tem a certeza que quer ativar esta sala?',
                        'data-method' => 'post',
                    ]);
                }
                return Html::button('<i class="fas fa-skull"></i>', [
                    'class' => 'btn btn-secondary btn-sm opacity-50',
                    'title' => 'Não pode eliminar sessões com lugares ocupados',
                    'style' => 'cursor: not-allowed;',
                ]);
            },
        ];

    }
}