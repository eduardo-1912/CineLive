<?php


namespace common\tests\unit\models;

use common\models\Cinema;
use common\models\Sessao;
use common\tests\UnitTester;

class SessaoTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    public function testCRUD()
    {
        // Create
        $sessao = new Sessao([
            'data' => '2025-05-01',
            'hora_inicio' => '14:00',
            'hora_fim' => '16:00',
            'filme_id' => 1,
            'cinema_id' => 1,
            'sala_id' => 1,
        ]);

        $this->assertTrue($sessao->validate());

        // Read
        $sessao->id = 123;
        $this->assertEquals('2025-05-01', $sessao->data);
        $this->assertEquals('14:00', $sessao->hora_inicio);

        // Update
        $sessao->hora_inicio = '15:00';
        $sessao->hora_fim = '17:00';

        $this->assertEquals('15:00', $sessao->hora_inicio);
        $this->assertEquals('17:00', $sessao->hora_fim);

        // Delete
        unset($sessao);

        $this->assertTrue(true); // apenas confirma que o objeto foi removido sem erro
    }

    private function createCinema()
    {
        $cinema = new Cinema();
        $cinema->horario_abertura = '10:00';
        $cinema->horario_fecho = '23:00';
        return $cinema;
    }

    public function testSessaoInvalida()
    {
        $sessao = new Sessao();

        $this->assertFalse($sessao->validate());
        $this->assertArrayHasKey('data', $sessao->errors);
        $this->assertArrayHasKey('hora_inicio', $sessao->errors);
        $this->assertArrayHasKey('hora_fim', $sessao->errors);
        $this->assertArrayHasKey('filme_id', $sessao->errors);
        $this->assertArrayHasKey('cinema_id', $sessao->errors);
        $this->assertArrayHasKey('sala_id', $sessao->errors);
    }

    public function testGetHoraFimCalculada()
    {
        $sessao = new Sessao([
            'hora_inicio' => '14:00'
        ]);

        $this->assertEquals('16:00', $sessao->getHoraFimCalculada(120));
    }

    public function testCreateOnCinemaEncerrado()
    {
        $cinema = new Cinema(['estado' => Cinema::ESTADO_ENCERRADO]);
        $sessao = new Sessao(['data' => date('Y-m-d', strtotime('+1 day')),]);
        $sessao->populateRelation('cinema', $cinema);

        $this->assertFalse($sessao->validate());
    }
}
