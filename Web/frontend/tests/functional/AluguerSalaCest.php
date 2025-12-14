<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class AluguerSalaCest
{
    public function _before(FunctionalTester $I)
    {
        // cliente autenticado com permissão criarAluguer
        $I->amLoggedInAs(14); // cliente1
    }

    public function createAluguerSala(FunctionalTester $I)
    {
        $data = date('Y-m-d', strtotime('+1 day'));

        $I->amOnRoute('aluguer-sala/create', [
            'cinema_id'   => 1,
            'data'        => $data,
            'hora_inicio' => '10:00',
            'hora_fim'    => '12:00',
        ]);

        $I->see('Pedido de aluguer');

        $I->seeElement('select[name="AluguerSala[sala_id]"]');

        $I->selectOption('select[name="AluguerSala[sala_id]"]', '1');
        $I->fillField('AluguerSala[tipo_evento]', 'Festa de aniversário');
        $I->fillField('AluguerSala[observacoes]', 'Pedido de teste funcional');

        $I->click('Enviar Pedido');

        $I->seeInCurrentUrl('/aluguer-sala/view');
        $I->see('Festa de aniversário');
    }



}
