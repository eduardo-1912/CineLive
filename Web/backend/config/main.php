<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'api' => [
            'class' => 'backend\modules\api\ModuleAPI',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '-',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                     '@vendor/hail812/yii2-adminlte3/src/views' => '@app/views',
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/default'],
                    'pluralize' => false,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/auth'],
                    'pluralize' => false,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/aluguer-sala'],
                    'pluralize' => false,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/perfil'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET'     => 'index',
                        'PUT'     => 'update',
                        'DELETE'  => 'delete',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/cinema'],
                    'extraPatterns' => [
                        'GET {id}/filmes' => 'filmes',
                        'GET list' => 'list',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/filme'],
                    'extraPatterns' => [
                        'GET {id}/sessoes' => 'sessoes',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/compra'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/genero'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['api/sessoes'],
                    'pluralize' => false,
                ],
            ],
        ],
    ],
    'params' => $params,
];
