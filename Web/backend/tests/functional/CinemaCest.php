<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\Cinema;

class CinemaCest
{
    public function createCinemaAsAdmin(FunctionalTester $I)
    {
        $I->amLoggedInAs(1); // admin
        $I->amOnRoute('cinema/create');

        $I->fillField('Cinema[nome]', 'CineLive Coimbra');
        $I->fillField('Cinema[rua]', 'Rua das Flores');
        $I->fillField('Cinema[codigo_postal]', '3004-504');
        $I->fillField('Cinema[cidade]', 'Coimbra');
        $I->fillField('Cinema[latitude]', '40.207437');
        $I->fillField('Cinema[longitude]', '-8.429603');
        $I->fillField('Cinema[email]', 'coimbra@cinelive.pt');
        $I->fillField('Cinema[telefone]', '239123456');
        $I->fillField('Cinema[horario_abertura]', '10:00');
        $I->fillField('Cinema[horario_fecho]', '23:30');
        $I->selectOption('Cinema[estado]', Cinema::ESTADO_ATIVO);

        $I->click('Guardar');

        $I->seeInCurrentUrl('cinema/view');
        $I->see('CineLive Coimbra');
    }

    public function gerenteCanSeeOwnCinema(FunctionalTester $I)
    {
        $I->amLoggedInAs(2); // gerente_leiria
        $I->amOnRoute('cinema/view', ['id' => 1]); // CineLive Leiria

        $I->see('CineLive Leiria');
        $I->see('Gerente');
    }

    public function gerenteCantSeeOtherCinema(FunctionalTester $I)
    {
        $I->amLoggedInAs(2); // gerente_leiria

        // Tenta aceder a outro cinema
        $I->amOnRoute('cinema/view', ['id' => 2]);  // CineLive Lisboa

        // Foi redirecionado para o cinema dele
        $I->seeInCurrentUrl('cinema/view');
        $I->see('CineLive Leiria');
    }
}