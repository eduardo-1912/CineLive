<?php

namespace common\tests\unit\models;

use common\models\Sala;
use common\models\Cinema;
use common\tests\UnitTester;

class SalaTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    private function createCinemaAtivo()
    {
        $cinema = new Cinema([

            'nome' => 'Cinema Teste',
            'rua' => 'Rua Teste',
            'codigo_postal' => '2400-149',
            'cidade' => 'Cidade Teste',
            'latitude' => '38.7169',
            'longitude' => '-9.1391',
            'email' => 'contato@cinemateste.com',
            'telefone' => '912345678',
            'horario_abertura' => '10:00:00',
            'horario_fecho' => '23:00:00',
            'estado' => Sala::ESTADO_ATIVA,
        ]);

        $cinema->save(false);
        return $cinema;
    }

    private function createSala(array $data = []): Sala
    {
        $cinema = $this->createCinemaAtivo();

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

        $this->assertEquals(
            ['A1', 'A2', 'A3', 'B1', 'B2', 'B3'],
            $sala->lugares
        );
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

        // Cria mock básico
        $sessao = $this->make(\common\models\Sessao::class, [
            'isEstadoAtiva' => function () { return true; }
        ]);

        $sala->populateRelation('sessoes', [$sessao]);
        $sala->populateRelation('aluguerSalas', []);

        $this->assertFalse($sala->isClosable());
    }
}
