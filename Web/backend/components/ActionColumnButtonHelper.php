<?php

namespace backend\components;

use common\models\Compra;
use yii\helpers\Html;
use common\models\Bilhete;

class ActionColumnButtonHelper
{
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

    public static function cinemaButtons()
    {
        return [
            'activate' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ENCERRADO) {
                    return Html::a('<i class="fas fa-toggle-on"></i>',
                        ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ATIVO], [
                        'class' => 'btn btn-sm ' . ($model->isActivatable() ? 'btn-success' : ' btn-secondary disabled'),
                        'title' => 'Ativar Sala',
                        'data-confirm' => 'Tem a certeza que quer ativar este cinema?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'close' => function ($url, $model) {
                if ($model->estado == $model::ESTADO_ATIVO) {
                    return Html::a('<i class="fas fa-toggle-off"></i>',
                        ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ENCERRADO], [
                        'class' => 'btn btn-sm ' . ($model->isClosable() ? 'btn-danger' : ' btn-secondary disabled'),
                        'title' => 'Encerrar Sala',
                        'data-confirm' => 'Tem a certeza que quer encerrar este cinema?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
        ];
    }

    public static function salaButtons()
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

    public static function compraButtons()
    {
        return [
            'cancel' => function ($url, $model) {
                if (!$model->isEstadoCancelada()) {
                    return Html::a('<i class="fas fa-ban"></i>', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_CANCELADA], [
                        'class' => 'btn btn-sm btn-danger',
                        'title' => 'Cancelar Compra',
                        'data-confirm' => 'Tem a certeza que quer cancelar esta compra?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
            'confirm' => function ($url, $model) {
                if (!$model->isEstadoConfirmada()) {
                    return Html::a('<i class="fas fa-check"></i>', ['change-status', 'id' => $model->id, 'estado' => $model::ESTADO_CONFIRMADA], [
                        'class' => 'btn btn-sm ' . ($model->isEstadoPendente() ? 'btn-success' : ' btn-secondary disabled'),
                        'title' => 'Confirmar Compra',
                        'data-confirm' => 'Tem a certeza que quer confirmar esta compra?',
                        'data-method' => 'post',
                    ]);
                }
                return '';
            },
        ];
    }

    public static function compraEstadoDropdown(Compra $model): string
    {
        // 🧩 Gerar os itens do dropdown (apenas estados permitidos)
        $items = '';
        foreach (Compra::optsEstado() as $key => $label) {
            // Ignorar o estado atual e o pendente
            if ($key === $model->estado || $key === Compra::ESTADO_PENDENTE) continue;

            $items .= Html::tag(
                'li',
                Html::a($label, ['compra/change-status', 'id' => $model->id, 'estado' => $key], [
                    'class' => 'dropdown-item',
                    'data' => [
                        'method' => 'post',
                        'confirm' => "Tem a certeza que quer alterar o estado para '{$label}'?",
                    ],
                ])
            );
        }

        // 🎨 Escolher classe do botão conforme o estado
        $btnClass = match ($model->estado) {
            Compra::ESTADO_CONFIRMADA => 'btn-success',
            Compra::ESTADO_CANCELADA => 'btn-danger',
            default => 'btn-secondary',
        };

        // 🚫 Desativar dropdown se a compra já estiver cancelada
        if ($model->estado === Compra::ESTADO_CANCELADA) {
            return Html::tag('div',
                Html::button($model->displayEstado(), [
                    'class' => "btn btn-sm {$btnClass}",
                    'style' => 'width: 100px; opacity: 0.7; cursor: not-allowed;',
                    'disabled' => true,
                    'title' => 'Compra cancelada — alteração de estado bloqueada',
                ]),
                ['class' => 'btn-group']
            );
        }

        // ✅ Dropdown normal
        return Html::tag('div',
            Html::button($model->displayEstado(), [
                'class' => "btn btn-sm {$btnClass} dropdown-toggle",
                'style' => 'width: 100px',
                'data-bs-toggle' => 'dropdown',
                'aria-expanded' => 'false',
            ]) .
            Html::tag('ul', $items, ['class' => 'dropdown-menu']),
            ['class' => 'btn-group']
        );
    }

    public static function bilheteButtons()
    {
        return [
            'changeStatus' => function ($url, $model) {
                // 🚫 Se a compra estiver cancelada, desativar o botão
                if ($model->compra && $model->compra->estado === \common\models\Compra::ESTADO_CANCELADA) {
                    $btnClass = match ($model->estado) {
                        \common\models\Bilhete::ESTADO_CONFIRMADO => 'btn-success',
                        \common\models\Bilhete::ESTADO_CANCELADO  => 'btn-danger',
                        default => 'btn-secondary',
                    };

                    return Html::tag('div',
                        Html::button($model->displayEstado(), [
                            'class' => "btn btn-sm {$btnClass}",
                            'style' => 'width: 100px; opacity: 0.6; cursor: not-allowed;',
                            'disabled' => true,
                            'title' => 'Compra cancelada — alteração de estado bloqueada'
                        ]),
                        ['class' => 'btn-group']
                    );
                }

                // 🔽 Caso normal — gerar dropdown
                $items = '';
                foreach (\common\models\Bilhete::optsEstado() as $estado => $label) {
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

                $btnClass = match ($model->estado) {
                    \common\models\Bilhete::ESTADO_CONFIRMADO => 'btn-success',
                    \common\models\Bilhete::ESTADO_CANCELADO  => 'btn-danger',
                    default => 'btn-secondary',
                };

                return Html::tag('div',
                    Html::button($model->displayEstado(), [
                        'class' => "btn btn-sm {$btnClass} dropdown-toggle",
                        'style' => 'width: 100px',
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