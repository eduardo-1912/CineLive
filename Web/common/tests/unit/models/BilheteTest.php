<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Bilhete;
use common\models\Compra;
use common\models\Sessao;
use common\tests\UnitTester;

class BilheteTest extends Unit
{
    protected UnitTester $tester;

    private function createBilhete(array $data = []): Bilhete
    {
        $sessao = $this->make(Sessao::class, ['isEstadoTerminada' => false,]);
        $compra = $this->make(Compra::class);
        $compra->populateRelation('sessao', $sessao);

        $defaults = [
            'compra_id' => 1,
            'lugar' => 'A1',
            'preco' => 7.5,
            'codigo' => Bilhete::gerarCodigo(),
            'estado' => Bilhete::ESTADO_PENDENTE,
        ];

        $bilhete = new Bilhete(array_merge($defaults, $data));
        $bilhete->populateRelation('compra', $compra);

        return $bilhete;
    }


    public function testBilheteValido()
    {
        $bilhete = $this->createBilhete();
        $this->assertTrue($bilhete->validate());
    }

    public function testEstadoInvalido()
    {
        $bilhete = $this->createBilhete(['estado' => 'invalido']);
        $this->assertFalse($bilhete->validate(['estado']));
    }

    public function testEstados()
    {
        $bilhete = $this->createBilhete();

        $this->assertTrue($bilhete->isEstadoPendente());

        $bilhete->setEstadoToConfirmado();
        $this->assertTrue($bilhete->isEstadoConfirmado());

        $bilhete->setEstadoToCancelado();
        $this->assertTrue($bilhete->isEstadoCancelado());
    }

    public function testDisplayEstado()
    {
        $bilhete = $this->createBilhete(['estado' => Bilhete::ESTADO_CONFIRMADO]);
        $this->assertEquals('Confirmado', $bilhete->displayEstado());
    }

    public function testGerarCodigo()
    {
        $codigo = Bilhete::gerarCodigo();

        $this->assertNotEmpty($codigo);
        $this->assertEquals(6, strlen($codigo));
    }

    public function testIsEditableQuandoPendenteESessaoNaoTerminada()
    {
        $bilhete = $this->createBilhete([
            'estado' => Bilhete::ESTADO_PENDENTE,
        ]);

        $this->assertTrue($bilhete->isEditable());
    }

    public function testIsNotEditableQuandoConfirmado()
    {
        $bilhete = $this->createBilhete([
            'estado' => Bilhete::ESTADO_CONFIRMADO,
        ]);

        $this->assertFalse($bilhete->isEditable());
    }
}
