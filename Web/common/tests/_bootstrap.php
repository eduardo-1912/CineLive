<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', __DIR__.'/../../');

require_once __DIR__ .  '/../../vendor/autoload.php';
require_once __DIR__ .  '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../config/bootstrap.php';
