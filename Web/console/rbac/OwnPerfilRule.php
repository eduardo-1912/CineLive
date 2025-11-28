<?php

namespace console\rbac;

use yii\rbac\Rule;

class OwnPerfilRule extends Rule
{
    public $name = 'isOwnPerfil';

    public function execute($user, $item, $params)
    {
        if (!isset($params['model'])) {
            return false;
        }

        $model = $params['model'];

        return $model->user_id == $user;
    }
}