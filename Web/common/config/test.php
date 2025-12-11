<?php
return [
    'id' => 'app-common-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test-key',
            'enableCsrfValidation' => false,
        ],
        'user' => [
            'identityClass' => \common\models\User::class,
            'enableSession' => false,
        ],
        'session' => [
            'class' => \yii\web\Session::class,
            'useCookies' => false,
        ],
    ],
];
