<?php
namespace backend\components;

use Yii;
use common\models\AluguerSala;

class SidebarHelper
{
    public static function getSidebarData()
    {
        $currentUser = Yii::$app->user;
        $profile = $currentUser->identity->profile;
        $userCinemaId = $profile->cinema_id;

        return [
            'profile' => $profile,
            'gerirUtilizadores' => $currentUser->can('gerirUtilizadores'),
            'gerirFuncionarios' => $currentUser->can('gerirFuncionarios'),
            'gerirCinemas' => $currentUser->can('gerirCinemas'),
            'gerirFilmes' => $currentUser->can('gerirFilmes'),
            'alugueresPendentes' => AluguerSala::find()
                ->where(['estado' => AluguerSala::ESTADO_PENDENTE, 'cinema_id' => $userCinemaId])
                ->exists(),
        ];
    }
}
