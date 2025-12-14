<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\Sala;

class SalaCest
{
    public function createSalaAsAdmin(FunctionalTester $I)
    {
        $I->amLoggedInAs(1); // admin
        $I->amOnRoute('sala/create?cinema_id=1'); // CineLive Leiria

        $I->fillField('Sala[numero]', 7);
        $I->fillField('Sala[num_filas]', 12);
        $I->fillField('Sala[num_colunas]', 10);
        $I->fillField('Sala[preco_bilhete]', 8);
        $I->selectOption('Sala[estado]', Sala::ESTADO_ATIVA);

        $I->click('Guardar');

        $I->seeInCurrentUrl('sala/view');
        $I->see('Sala 7');
    }

    public function createSalaAsGerente(FunctionalTester $I)
    {
        $I->amLoggedInAs(2); // gerente_leiria
        $I->amOnRoute('sala/create');

        $I->fillField('Sala[numero]', 7);
        $I->fillField('Sala[num_filas]', 12);
        $I->fillField('Sala[num_colunas]', 10);
        $I->fillField('Sala[preco_bilhete]', 8);
        $I->selectOption('Sala[estado]', Sala::ESTADO_ATIVA);

        $I->click('Guardar');

        $I->seeInCurrentUrl('sala/view');
        $I->see('Sala 7');
    }
}