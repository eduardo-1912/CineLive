<?php

namespace frontend\helpers;

use Yii;
use yii\web\Cookie;

class CookieHelper
{
    public static function set(string $name, mixed $value, int $days = 180): void
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => time() + 3600 * 24 * $days,
        ]));
    }

    public static function get(string $name, mixed $default = null): mixed
    {
        $cookies = Yii::$app->request->cookies;

        return $cookies->has($name) ? $cookies->getValue($name) : $default;
    }

    public static function has(string $name): bool
    {
        return Yii::$app->request->cookies->has($name);
    }

    public static function delete(string $name): void
    {
        Yii::$app->response->cookies->remove($name);
    }
}