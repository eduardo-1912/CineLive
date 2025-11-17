<?php

namespace backend\modules\api;

use Yii;
use yii\web\Response;

/**
 * api module definition class
 */
class ModuleAPI extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Forçar resposta em JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Desativar sessões (API deve ser stateless)
        Yii::$app->user->enableSession = false;

        // Não redirecionar para página de login
        Yii::$app->user->loginUrl = null;
    }
}
