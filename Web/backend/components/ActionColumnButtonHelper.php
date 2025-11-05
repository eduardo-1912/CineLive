<?php

namespace backend\components;

use yii\helpers\Html;
use common\models\Bilhete;

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

    // TODO: ELIMINAR
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

    // TOGGLES PARA CINEMAS E SALAS
    public static function toggleButtons()
    {
        return [
            'activate' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ENCERRADA) {
                    return Html::a('<i class="fas fa-toggle-on"></i>', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ATIVA], [
                        'class' => 'btn btn-sm ' . ($model->isActivatable() ? 'btn-success' : ' btn-secondary disabled'),
                        'title' => 'Ativar Sala',
                        'data-confirm' => 'Tem a certeza que quer ativar esta sala?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'close' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ATIVA) {
                    return Html::a('<i class="fas fa-toggle-off"></i>',  ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ENCERRADA], [
                        'class' => 'btn btn-sm ' . ($model->isClosable() ? 'btn-danger' : ' btn-secondary disabled'),
                        'title' => 'Encerrar Sala',
                        'data-confirm' => 'Tem a certeza que quer encerrar esta sala?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
        ];
    }

    // FILMES
    public static function filmeButtons()
    {
        return [
            'createSessao' => function ($url, $model) {
                return Html::a('<i class="fas fa-calendar-plus"></i>', ['sessao/create', 'filme_id' => $model->id], [
                    'class' => 'btn btn-sm ' . ($model->estado == $model::ESTADO_EM_EXIBICAO ? 'btn-success' : ' btn-secondary disabled'),
                    'title' => 'Criar Sessão',
                    'data-method' => 'post',
                ]);
            },
        ];
    }

    // COMPRAS
    public static function compraButtons()
    {
        return [
            'sessao' => function ($url, $model) {
                $sessao = $model->getBilhetes()->one()->sessao;

                if (!$sessao) {
                    return ''; // Se não tiver sessão, não mostra botão
                }

                return Html::a(
                    '<i class="fas fa-calendar-day"></i>',
                    ['sessao/view', 'id' => $sessao->id],
                    [
                        'class' => 'btn btn-sm btn-success',
                        'title' => 'Ver Sessão',
                    ]
                );
            },

            'changeStatus' => function ($url, $model) {


                $items = '';
                foreach (Bilhete::optsEstado() as $estado => $label) {
                    // Ignorar o estado atual (opcional)
                    if ($model->estado === $estado) continue;

                    $items .= Html::tag('li',
                        Html::a($label, ['bilhete/change-status', 'id' => $model->id, 'estado' => $estado], [
                            'class' => 'dropdown-item',
                            'data' => [
                                'method' => 'post',
                                'confirm' => "Tem a certeza que quer alterar o estado para '{$label}'?",
                            ],
                        ])
                    );
                }

                return Html::tag('div',
                    Html::button($model->displayEstado(), [
                        'class' => 'btn btn-sm btn-secondary dropdown-toggle',
                        'data-bs-toggle' => 'dropdown',
                        'aria-expanded' => 'false'
                    ]) .
                    Html::tag('ul', $items, ['class' => 'dropdown-menu']),
                    ['class' => 'btn-group']
                );
            }

        ];
    }
}