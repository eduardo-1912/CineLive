<?php


namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Filme;
use common\tests\UnitTester;

class FilmeTest extends Unit
{
    protected UnitTester $tester;

    public function createFilme(array $data = []): Filme
    {
        $defaults = [
            'titulo' => 'Interstellar',
            'sinopse' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'duracao' => 120,
            'rating' => 'M6',
            'estreia' => '2025-11-20',
            'idioma' => 'Inglês',
            'realizacao' => 'Christopher Nolan',
            'trailer_url' => 'https://www.youtube.com/watch?v=2LqzF5WauAw',
            'estado' => Filme::ESTADO_BREVEMENTE,
        ];

        return new Filme(array_merge($defaults, $data));
    }

    public function testCRUD()
    {
        // Create
        $filme = $this->createFilme();
        $this->assertTrue($filme->save());
        $this->assertNotNull($filme->id);

        // Read
        $this->assertNotNull(Filme::findOne($filme->id));

        // Update
        $filme->estado = Filme::ESTADO_TERMINADO;
        $this->assertTrue($filme->save());
        $this->assertEquals(Filme::ESTADO_TERMINADO, Filme::findOne($filme->id)->estado);

        // Delete
        $filme->delete();
        $this->assertNull(Filme::findOne($filme->id));
    }

    public function testDuracaoInvalida()
    {
        $filme = $this->createFilme(['duracao' => -5]);
        $this->assertFalse($filme->validate(['duracao']));
    }

    public function testRatingInvalido()
    {
        $filme = $this->createFilme(['rating' => 'invalido']);
        $this->assertFalse($filme->validate(['rating']));
    }

    public function testEstadoInvalido()
    {
        $filme = $this->createFilme(['estado' => 'invalido']);
        $this->assertFalse($filme->validate(['estado']));
    }

    public function testIsEstadoTerminado()
    {
        $filme = $this->createFilme();
        $filme->estado = Filme::ESTADO_TERMINADO;
        $this->assertTrue($filme->isEstadoTerminado());
    }

    public function testSetEstadoToBrevemente()
    {
        $filme = $this->createFilme();
        $filme->setEstadoToBrevemente();
        $this->assertEquals(Filme::ESTADO_BREVEMENTE, $filme->estado);
    }
}
