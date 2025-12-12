<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\User;
use Yii;
use common\models\LoginForm;
use common\fixtures\UserFixture;

class LoginFormTest extends Unit
{
    protected $tester;

    public function testLoginNoUser()
    {
        $model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        verify($model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword()
    {
        $model = new LoginForm([
            'username' => 'testuser',
            'password' => 'wrong_password',
        ]);

        verify($model->login())->false();
        verify($model->errors)->arrayHasKey('password');
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginValidate()
    {
        $model = new LoginForm([
            'username' => 'cliente1',
            'password' => '12345678',
        ]);

        verify($model->validate())->true();
    }
}
