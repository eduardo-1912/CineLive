<?php

namespace backend\components;


use common\models\AluguerSala;
use common\models\Filme;
use Yii;
use yii\helpers\Html;
use common\models\User;
use common\models\Bilhete;
use common\models\Compra;

class ActionColumnButtonHelper
{
    public static function userButtons()
    {
        return [
            'softDelete' => function ($url, $model) {
                return Html::a('<i class="fas fa-trash"></i>', ['change-status', 'id' => $model->id, 'estado' => $model::STATUS_DELETED], [
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
                return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm ' . ($model->id == Yii::$app->user->identity->id ? 'btn-secondary disabled' : 'btn-danger'),
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

    public static function userEstadoDropdown(User $model): string
    {
        $items = '';
        foreach (User::optsStatus() as $estado => $label) {

            // IGNORAR O ESTADO ATUAL
            if ($estado === $model->status) continue;

            // SE USER ATUAL FOR GERENTE, NÃO MOSTRAR 'ELIMINADO'
            if (!Yii::$app->user->can('gerirUtilizadores')) {
                if ($estado === User::STATUS_DELETED) continue;
            }

            $items .=
            '<li>' .
                Html::a($label, ['user/change-status', 'id' => $model->id, 'estado' => $estado], [
                    'class' => 'dropdown-item',
                    'data' => [
                        'method' => 'post',
                        'confirm' => "Tem a certeza que quer alterar o estado do utilizador para '$label'?",
                    ]]) .
            '</li>';
        }

        $btnClass = match ($model->status) {
            User::STATUS_ACTIVE => '',
            User::STATUS_INACTIVE => 'text-danger',
            User::STATUS_DELETED => 'text-secondary font-italic',
            default => '',
        };

        return '
        <div class="btn-group"> ' .
            Html::button($model->displayStatus(), [
                'class' => "btn p-0 fs-6 text-align-start text-start $btnClass dropdown-toggle border-0",
                'data-bs-toggle' => 'dropdown',
                'aria-expanded' => 'false',]) . '
            <ul class="dropdown-menu">' . $items . '</ul>
        </div>';
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
                    return Html::a('<i class="fas fa-toggle-on"></i>', ['sala/change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ATIVA], [
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
                    return Html::a('<i class="fas fa-toggle-off"></i>',  ['sala/change-status', 'id' => $model->id, 'estado' => $model::ESTADO_ENCERRADA], [
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

    public static function filmeEstadoDropdown(Filme $model): string
    {
        $currentUser = Yii::$app->user;

        $btnClass = match ($model->estado) {
            Filme::ESTADO_BREVEMENTE => 'text-secondary',
            Filme::ESTADO_TERMINADO => 'text-secondary font-italic',
            default => '',
        };

        if (!$currentUser->can('gerirFilmes')) {
            return Html::tag('span', Html::encode($model->displayEstado()), ['class' => "fs-6 $btnClass"]);
        }

        $items = '';
        foreach (Filme::optsEstado() as $estado => $label) {
            if ($estado === $model->estado) continue;

            $items .= '<li>' .
                Html::a($label, ['filme/change-status', 'id' => $model->id, 'estado' => $estado], [
                    'class' => 'dropdown-item',
                    'data' => [
                        'method' => 'post',
                        'confirm' => "Tem a certeza que quer alterar o estado do filme para '$label'?",
                    ],
                ]) .
                '</li>';
        }

        return '
        <div class="btn-group">' .
            Html::button($model->displayEstado(), [
                'class' => "btn p-0 fs-6 text-align-start text-start $btnClass dropdown-toggle border-0",
                'data-bs-toggle' => 'dropdown',
                'aria-expanded' => 'false',
            ]) . '
            <ul class="dropdown-menu">' . $items . '</ul>
        </div>';
    }

    public static function compraButtons()
    {
        return [
            'confirmarBilhetes' => function ($url, $model) {
                return Html::a('<i class="fas fa-check-double"></i>', ['compra/confirm-all-tickets', 'id' => $model->id], [
                    'class' => 'btn btn-sm ' .($model->isEstadoConfirmada() && !$model->isTodosBilhetesConfirmados() && !$model->sessao->isEstadoTerminada()
                    ? 'btn-success' : 'btn-secondary disabled'),
                    'title' => 'Confirmar Bilhetes',
                    'data-confirm' => 'Tem a certeza que quer confirmar todos os bilhetes desta compra?',
                    'data-method' => 'post',
                ]);
            },
        ];
    }

    public static function compraEstadoDropdown(Compra $model): string
    {
        $currentUser = Yii::$app->user;

        $btnClass = match ($model->estado) {
            Compra::ESTADO_CANCELADA => 'text-danger',
            default => '',
        };

        if (!$currentUser->can('gerirCompras') || $model->sessao->isEstadoTerminada() || $model->isEstadoCancelada()) {
            return Html::tag('span', Html::encode($model->displayEstado()), ['class' => "fs-6 $btnClass"]);
        }

        $items = '';
        foreach (Compra::optsEstado() as $estado => $label) {
            if ($estado === $model->estado) continue;

            $items .= '<li>' .
                Html::a($label, ['compra/change-status', 'id' => $model->id, 'estado' => $estado], [
                    'class' => 'dropdown-item',
                    'data' => [
                        'method' => 'post',
                        'confirm' => "Tem a certeza que quer alterar o estado da compra para '$label'?",
                    ],
                ]) .
                '</li>';
        }

        return '
        <div class="btn-group">' .
            Html::button($model->displayEstado(), [
                'class' => "btn p-0 fs-6 text-align-start text-start $btnClass dropdown-toggle border-0",
                'data-bs-toggle' => 'dropdown',
                'aria-expanded' => 'false',
            ]) . '
            <ul class="dropdown-menu">' . $items . '</ul>
        </div>';
    }

    public static function bilheteEstadoDropdown()
    {
        return [
            'changeStatus' => function ($url, $model) {
                if ($model->compra && $model->compra->estado === Compra::ESTADO_CANCELADA || $model->compra->sessao->isEstadoTerminada()) {
                    $btnClass = match ($model->estado) {
                        Bilhete::ESTADO_CONFIRMADO => 'btn-success',
                        Bilhete::ESTADO_CANCELADO  => 'btn-danger',
                        default => 'btn-secondary',
                    };

                    return Html::tag('div',
                        Html::button($model->displayEstado(), [
                            'class' => "btn btn-sm {$btnClass}",
                            'style' => 'width: 100px; opacity: 0.6; cursor: not-allowed;',
                            'disabled' => true,
                        ]),
                        ['class' => 'btn-group']
                    );
                }

                $items = '';
                foreach (Bilhete::optsEstado() as $estado => $label) {
                    if ($model->estado === $estado) continue;
                    if ($model->estado === Bilhete::ESTADO_CANCELADO && $estado === Bilhete::ESTADO_CONFIRMADO) continue;

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
                    Bilhete::ESTADO_CONFIRMADO => 'btn-success',
                    Bilhete::ESTADO_CANCELADO  => 'btn-danger',
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

    public static function aluguerEstadoDropdown($model)
    {
        if (
            $model->isEstadoCancelado() ||
            $model->isEstadoADecorrer() ||
            $model->isEstadoTerminado()
        ) {
            return $model->getEstadoFormatado();
        }

        $items = '';
        foreach ([$model::ESTADO_CONFIRMADO => 'Confirmar', $model::ESTADO_CANCELADO  => 'Cancelar',] as $estado => $label) {

            if ($model->estado === $estado) continue;

            $items .= Html::tag('li',
                Html::a($label, ['aluguer-sala/change-status', 'id' => $model->id, 'estado' => $estado], [
                    'class' => 'dropdown-item',
                    'data' => [
                        'method' => 'post',
                        'confirm' => "Tem a certeza que quer alterar o estado para '{$label}'?",
                    ],
                ])
            );
        }

        $btnClass = match ($model->estado) {
            $model::ESTADO_PENDENTE   => 'text-primary',
            default => '',
        };

        return '
        <div class="btn-group">
            <button class="btn text-start p-0 border-0 ' . $btnClass . ' dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    style="width: 100px;">
                ' . $model->displayEstado() . '
            </button>
            <ul class="dropdown-menu">
                ' . $items . '
            </ul>
        </div>
        ';
    }
}