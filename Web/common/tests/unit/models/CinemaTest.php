<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Cinema;
use common\tests\UnitTester;

class CinemaTest extends Unit
{
    protected UnitTester $tester;

    private function createCinema(array $data = []): Cinema
    {
        $defaults = [
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
        ];

        return new Cinema(array_merge($defaults, $data));
    }

    public function testCRUD()
    {
        // Create
        $cinema = $this->createCinema();
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

    public function testGetMorada()
    {
        $cinema = $this->createCinema();
        $this->assertEquals('Rua Dr. Francisco Sá Carneiro Nº25, 2400-149 Leiria', $cinema->morada);
    }

    public function testIsEstadoAtivo()
    {
        $cinema = $this->createCinema();
        $this->assertTrue($cinema->isEstadoAtivo());

        $cinema->estado = Cinema::ESTADO_ENCERRADO;
        $this->assertFalse($cinema->isEstadoAtivo());
    }

    public function testSetEstadoToEncerrado()
    {
        $cinema = $this->createCinema();
        $cinema->setEstadoToEncerrado();

        $this->assertEquals(Cinema::ESTADO_ENCERRADO, $cinema->estado);
    }
}
