<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\UnauthorizedHttpException;

class GeneroController extends ActiveController
{
    public $modelClass = 'common\models\Genero';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ],
            'except' => ['index', 'view'],
        ];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['index', 'view'])) {
            return true;
        }

        if (!Yii::$app->user->can('gerirGeneros')) {
            throw new UnauthorizedHttpException("Não tem permissão para gerir géneros.");
        }

        return false;
    }
}