<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class SessaoCest
{
    public function createSessaoAsAdmin(FunctionalTester $I)
    {
        $I->amLoggedInAs(1); // admin

        $data = date('Y-m-d', strtotime('+1 day'));
        $I->amOnRoute('/sessao/create', [
            'cinema_id'   => 1,      // CineLive Leiria
            'filme_id'    => 1,      // filme em exibição
            'data'        => $data,
            'hora_inicio' => '15:00',
        ]);

        $I->see('Criar Sessão');
        $I->seeElement('select[name="Sessao[sala_id]"]');
        $I->selectOption('Sessao[sala_id]', '1');
        $I->click('Guardar');

        $I->seeInCurrentUrl('/sessao/view');
        $I->see('15:00');
    }

    public function gerenteCanCreateSessaoInOwnCinema(FunctionalTester $I)
    {
        $data = date('Y-m-d', strtotime('+1 day'));

        $I->amLoggedInAs(2); // gerente_leiria

        $I->amOnRoute('/sessao/create', [
            'filme_id'    => 8,
            'data'        => $data,
            'hora_inicio' => '18:00',
        ]);

        $I->see('Criar Sessão');
        $I->seeElement('select[name="Sessao[sala_id]"]');

        $I->selectOption('Sessao[sala_id]', '1');
        $I->click('Guardar');

        $I->seeInCurrentUrl('/sessao/view');
    }
}
