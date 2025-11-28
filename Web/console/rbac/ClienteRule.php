<?php

namespace console\rbac;

use yii\rbac\Rule;

class ClienteRule extends Rule
{
    public $name = 'isCliente';

    public function execute($user, $item, $params)
    {
        if (!isset($params['model'])) {
            return false;
        }

        $model = $params['model'];

        return $model->cliente_id == $user;
    }
}