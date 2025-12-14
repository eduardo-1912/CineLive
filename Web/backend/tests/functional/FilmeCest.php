<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\models\Filme;

class FilmeCest
{
    public function createFilmeAsAdmin(FunctionalTester $I)
    {
        $I->amLoggedInAs(1); // admin
        $I->amOnRoute('/filme/create');
        $I->see('Criar Filme');

        $I->fillField('Filme[titulo]', 'Interstellar');
        $I->fillField('Filme[sinopse]', 'Lorem ipsum.');
        $I->fillField('Filme[duracao]', '120');
        $I->selectOption('Filme[generosSelecionados][]', ['1', '2']);

        $I->selectOption('Filme[rating]', Filme::RATING_M12);
        $I->fillField('Filme[estreia]', date('Y-m-d'));
        $I->fillField('Filme[idioma]', 'Inglês');
        $I->fillField('Filme[realizacao]', 'Wachowski');
        $I->fillField('Filme[trailer_url]', 'https://youtube.com/trailer');
        $I->selectOption('Filme[estado]', Filme::ESTADO_EM_EXIBICAO);

        $I->click('Guardar');

        // redirect para view
        $I->seeInCurrentUrl('/filme/view');

        $I->see('Interstellar');
        $I->see('Lorem ipsum.');
        $I->see('120');
    }
}