<?php


namespace common\tests\unit\models;

use common\models\Filme;
use common\tests\UnitTester;

class FilmeTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    public function testCRUD()
    {
        // Create
        $filme = new Filme([
            'titulo' => 'Interstellar',
            'sinopse' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'duracao' => 120,
            'rating' => 'M6',
            'estreia' => '2025-11-20',
            'idioma' => 'Inglês',
            'realizacao' => 'Christopher Nolan',
            'trailer_url' => 'https://www.youtube.com/watch?v=2LqzF5WauAw',
            'estado' => Filme::ESTADO_BREVEMENTE,
        ]);
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

    public function testFilmeValido()
    {
        $filme = new Filme([
            'titulo' => 'Interstellar',
            'sinopse' => 'Lorem ipsum',
            'duracao' => 120,
            'rating' => Filme::RATING_M12,
            'estreia' => '2025-11-20',
            'idioma' => 'Inglês',
            'realizacao' => 'Christopher Nolan',
            'trailer_url' => 'https://www.youtube.com/watch?v=2LqzF5WauAw',
            'estado' => Filme::ESTADO_BREVEMENTE,
        ]);

        $this->assertTrue($filme->validate());
    }

    public function testDuracaoInvalida()
    {
        $filme = new Filme([
            'titulo' => 'Interstellar',
            'sinopse' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'duracao' => -5,
            'rating' => 'M6',
            'estreia' => '2025-11-20',
            'idioma' => 'Inglês',
            'realizacao' => 'Christopher Nolan',
            'trailer_url' => 'https://www.youtube.com/watch?v=2LqzF5WauAw',
            'estado' => Filme::ESTADO_BREVEMENTE,
        ]);

        $this->assertFalse($filme->validate(['duracao']));
    }

    public function testRatingInvalido()
    {
        $filme = new Filme([
            'titulo' => 'Interstellar',
            'sinopse' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'duracao' => 120,
            'rating' => 'rating_invalido',
            'estreia' => '2025-11-20',
            'idioma' => 'Inglês',
            'realizacao' => 'Christopher Nolan',
            'trailer_url' => 'https://www.youtube.com/watch?v=2LqzF5WauAw',
            'estado' => Filme::ESTADO_BREVEMENTE,
        ]);

        $this->assertFalse($filme->validate(['rating']));
    }

    public function testEstadoInvalido()
    {
        $filme = new Filme([
            'titulo' => 'Interstellar',
            'sinopse' => 'Lorem ipsum dolor',
            'duracao' => 120,
            'rating' => Filme::RATING_M6,
            'estreia' => '2025-11-20',
            'idioma' => 'Inglês',
            'realizacao' => 'Christopher Nolan',
            'trailer_url' => 'https://www.youtube.com/watch?v=2LqzF5WauAw',
            'estado' => 'estado_invalido'
        ]);

        $this->assertFalse($filme->validate(['estado']));
    }

    public function testIsEstadoTerminado()
    {
        $filme = new Filme();
        $filme->estado = Filme::ESTADO_TERMINADO;

        $this->assertTrue($filme->isEstadoTerminado());
    }

    public function testSetEstadoToBrevemente()
    {
        $filme = new Filme();
        $filme->setEstadoToBrevemente();

        $this->assertEquals(Filme::ESTADO_BREVEMENTE, $filme->estado);
    }
}
