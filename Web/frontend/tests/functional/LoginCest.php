<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class LoginCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
    }

    public function loginAsCliente(FunctionalTester $I)
    {
        $I->fillField('Username', 'cliente1');
        $I->fillField('Password', '12345678');

        $I->click('login-button');
        $I->dontSeeLink('Login');
    }
}
