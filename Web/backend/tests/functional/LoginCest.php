<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;

/**
 * Class LoginCest
 */
class LoginCest
{
    public function loginAsAdmin(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'admin');
        $I->fillField('Password', 'admin123');
        $I->click('login-button');
        $I->see('Dashboard');
        $I->see('CineLive (Administrador)');
    }

    public function loginAsGerente(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'gerente_leiria');
        $I->fillField('Password', '12345678');
        $I->click('login-button');
        $I->see('Dashboard');
        $I->see('CineLive Leiria (Gerente)');
    }

    public function loginAsGerenteSemCinema(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'gerente_porto');
        $I->fillField('Password', '12345678');
        $I->click('login-button');
        $I->dontSee('Dashboard');
    }

    public function loginAsFuncionario(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'funcionario1_leiria');
        $I->fillField('Password', '12345678');
        $I->click('login-button');
        $I->see('Dashboard');
        $I->see('CineLive Leiria (Funcionário)');
    }

    public function loginAsFuncionarioSemCinema(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'funcionario1_porto');
        $I->fillField('Password', '12345678');
        $I->click('login-button');
        $I->dontSee('Dashboard');
    }

    public function loginAsCliente(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'cliente1');
        $I->fillField('Password', '12345678');
        $I->click('login-button');
        $I->dontSee('Dashboard');
    }

    public function loginSemRole(FunctionalTester $I)
    {
        $I->amOnRoute('/site/login');
        $I->fillField('Username', 'cliente2');
        $I->fillField('Password', '12345678');
        $I->click('login-button');
        $I->dontSee('Dashboard');
    }
}
