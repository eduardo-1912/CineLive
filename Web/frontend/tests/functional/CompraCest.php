<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class CompraCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amLoggedInAs(14); // cliente1
    }

    public function createCompra(FunctionalTester $I)
    {
        $I->amOnRoute('compra/create?sessao_id=1');
        $I->see('Comprar Bilhetes');
        $I->canSeeLink('1');
    }
}