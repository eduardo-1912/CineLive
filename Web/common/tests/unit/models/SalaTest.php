<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Sala;
use common\models\Cinema;
use common\models\Sessao;
use common\tests\UnitTester;

class SalaTest extends Unit
{
    protected UnitTester $tester;

    private function createCinema(): Cinema
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

        $cinema->save();
        return $cinema;
    }

    private function createSala(array $data = []): Sala
    {
        $cinema = $this->createCinema();

        $defaults = [
            'cinema_id' => $cinema->id,
            'numero' => 1,
            'num_filas' => 5,
            'num_colunas' => 10,
            'preco_bilhete' => 7.5,
            'estado' => Sala::ESTADO_ATIVA,
        ];

        return new Sala(array_merge($defaults, $data));
    }

    public function testCRUD()
    {
        // Create
        $sala = $this->createSala();
        $this->assertTrue($sala->save());
        $this->assertNotNull($sala->id);

        // Read
        $this->assertNotNull(Sala::findOne($sala->id));

        // Update
        $sala->estado = Sala::ESTADO_ENCERRADA;
        $this->assertTrue($sala->save());
        $this->assertEquals(Sala::ESTADO_ENCERRADA, Sala::findOne($sala->id)->estado);

        // Delete
        $sala->delete();
        $this->assertNull(Sala::findOne($sala->id));
    }

    public function testSalaValida()
    {
        $sala = $this->createSala();
        $this->assertTrue($sala->validate());
    }

    public function testNumFilasInvalido()
    {
        $sala = $this->createSala(['num_filas' => 30]); // max = 26
        $this->assertFalse($sala->validate(['num_filas']));
    }

    public function testNumeroLugares()
    {
        $sala = $this->createSala(['num_filas' => 4, 'num_colunas' => 5]);
        $this->assertEquals(20, $sala->numeroLugares);
    }

    public function testLugaresGerados()
    {
        $sala = $this->createSala(['num_filas' => 2, 'num_colunas' => 3]);
        $this->assertEquals(['A1', 'A2', 'A3', 'B1', 'B2', 'B3'], $sala->lugares);
    }

    public function testNomeSala()
    {
        $sala = $this->createSala(['numero' => 7]);
        $this->assertEquals('Sala 7', $sala->nome);
    }

    public function testIsClosableSemSessoesNemAlugueres()
    {
        $sala = $this->createSala(['estado' => Sala::ESTADO_ATIVA]);

        $sala->populateRelation('sessoes', []);
        $sala->populateRelation('aluguerSalas', []);

        $this->assertTrue($sala->isClosable());
    }

    public function testIsNotClosableQuandoTemSessoes()
    {
        $sala = $this->createSala(['estado' => Sala::ESTADO_ATIVA]);

        $sessao = $this->make(Sessao::class, ['isEstadoAtiva' => true]);

        $sala->populateRelation('sessoes', [$sessao]);
        $sala->populateRelation('aluguerSalas', []);

        $this->assertFalse($sala->isClosable());
    }
}
