<?php

namespace console\rbac;

use common\models\User;
use Yii;
use yii\rbac\Rule;

class MatchCinemaRule extends Rule
{
    public $name = 'matchCinema';

    public function execute($user, $item, $params)
    {
        if (!isset($params['model'])) {
            return false;
        }

        $model = $params['model'];

        $userCinemaId = User::findOne($user)->profile->cinema_id;

        if (!$userCinemaId) {
            return false;
        }

        // Cinema
        if (isset($model->id)) {
            return $model->id == $userCinemaId;
        }

        // Sala
        if (isset($model->cinema_id)) {
            return $model->cinema_id == $userCinemaId;
        }

        // Sessão
        if (isset($model->sala->cinema_id)) {
            return $model->sala->cinema_id == $userCinemaId;
        }

        // Bilhete
        if (isset($model->compra->sessao->cinema_id)) {
            return $model->compra->sessao->cinema_id == $userCinemaId;
        }

        return false;
    }
}