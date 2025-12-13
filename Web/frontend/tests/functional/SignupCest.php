<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class SignupCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/signup');
    }

    public function signup(FunctionalTester $I)
    {
        $I->fillField('SignupForm[username]', 'newcliente');
        $I->fillField('SignupForm[password]', '12345678');
        $I->fillField('SignupForm[email]', 'newcliente@mail.com');
        $I->fillField('SignupForm[nome]', 'New Cliente');
        $I->fillField('SignupForm[telemovel]', '123456789');

        $I->click('signup-button');

        $I->dontSeeLink('Login');
    }
}
