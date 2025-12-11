<?php


namespace common\tests\unit\models;

use common\models\Cinema;
use common\tests\UnitTester;

class CinemaTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    public function testCRUD()
    {
        // Create
        $cinema = new Cinema([
            'nome' => 'CineLive Leiria',
            'rua' => 'Rua Dr. Francisco Sá Carneiro Nº25',
            'codigo_postal' => '2400-149',
            'cidade' => 'Leiria',
            'latitude' => 39.743620,
            'longitude' => -8.807049,
            'email' => 'leiria@cinelive.pt',
            'telefone' => '244123456',
            'horario_abertura' => '10:00',
            'horario_fecho' => '23:30',
            'estado' => Cinema::ESTADO_ATIVO,
        ]);
        $this->assertTrue($cinema->save());

        // Read
        $this->assertNotNull(Cinema::findOne($cinema->id));

        // Update
        $cinema->horario_abertura = '10:00';
        $this->assertTrue($cinema->save());
        $this->assertEquals('10:00', $cinema->horario_abertura);

        // Delete
        $cinema->delete();
        $this->assertNull(Cinema::findOne($cinema->id));
    }

    public function testCinemaValido()
    {
        $cinema = new Cinema([
            'nome' => 'CineLive Leiria',
            'rua' => 'Rua Dr. Francisco Sá Carneiro Nº25',
            'codigo_postal' => '2400-149',
            'cidade' => 'Leiria',
            'latitude' => 39.743620,
            'longitude' => -8.807049,
            'email' => 'leiria@cinelive.pt',
            'telefone' => '244123456',
            'horario_abertura' => '10:00',
            'horario_fecho' => '23:30',
            'estado' => Cinema::ESTADO_ATIVO,
        ]);

        $this->assertTrue($cinema->validate());
    }

    public function testGetMorada()
    {
        $cinema = new Cinema([
            'rua' => 'Rua Dr. Francisco Sá Carneiro Nº25',
            'codigo_postal' => '2400-149',
            'cidade' => 'Leiria',
        ]);

        $this->assertEquals('Rua Dr. Francisco Sá Carneiro Nº25, 2400-149 Leiria', $cinema->morada);
    }

    public function testIsEstadoAtivo()
    {
        $cinema = new Cinema(['estado' => Cinema::ESTADO_ATIVO]);
        $this->assertTrue($cinema->isEstadoAtivo());

        $cinema->estado = Cinema::ESTADO_ENCERRADO;
        $this->assertFalse($cinema->isEstadoAtivo());
    }

    public function testSetEstadoToEncerrado()
    {
        $cinema = new Cinema();
        $cinema->setEstadoToEncerrado();

        $this->assertEquals(Cinema::ESTADO_ENCERRADO, $cinema->estado);
    }
}
