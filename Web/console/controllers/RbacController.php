<?php

namespace console\controllers;

use common\models\User;
use console\rbac\ClienteRule;
use console\rbac\MatchCinemaRule;
use console\rbac\OwnPerfilRule;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        // Regras
        $ownPerfilRule = new OwnPerfilRule();
        $auth->add($ownPerfilRule);

        $clienteRule = new ClienteRule();
        $auth->add($clienteRule);

        $matchCinemaRule = new MatchCinemaRule();
        $auth->add($matchCinemaRule);

        // Criar Permissões
        $permissoes = [
            // Cliente
            'verPerfil' => 'Ver o seu perfil',
            'editarPerfil' => 'Editar o seu perfil',
            'eliminarPerfil' => 'Eliminar o seu perfil',
            'criarCompra' => 'Criar uma compra',
            'verCompras' => 'Ver as suas compras',
            'criarAluguer' => 'Criar pedido de aluguer de sala',
            'verAlugueres' => 'Ver os seus aluguers',
            'eliminarAluguer' => 'Eliminar pedido de aluguer de sala pendente',

            // Funcionário
            'verCinema' => 'Ver o seu cinema',
            'verSalasCinema' => 'Ver salas de um cinema',
            'verSessoesCinema' => 'Ver sessões de um cinema',
            'verComprasCinema' => 'Ver compras de um cinema',
            'confirmarBilhetesCinema' => 'Confirmar bilhetes de um cinema',
            'verAlugueresCinema' => 'Ver alugueres de sala de um cinema',
            'verEstatisticasCinema' => 'Ver estatísticas de um cinema',

            // Gerente
            'verFuncionariosCinema'  => 'Ver funcionários do seu cinema',
            'criarFuncionarioCinema' => 'Criar funcionário para o seu cinema',
            'alterarEstadoFuncionario' => 'Ativar/Desativar funcionários do cinema',
            'editarCinema' => 'Editar dados gerais do cinema',
            'gerirSalasCinema' => 'Gerir salas do cinema',
            'gerirSessoesCinema' => 'Gerir sessões do cinema',
            'gerirAlugueresCinema' => 'Gerir alugueres do cinema',

            // Admin
            'gerirUtilizadores' => 'Gerir todos os utilizadores',
            'gerirCinemas' => 'Gerir todos os cinemas',
            'gerirSalas' => 'Gerir todas as salas',
            'gerirFilmes' => 'Gerir filmes',
            'gerirGeneros' => 'Gerir géneros',
            'gerirSessoes' => 'Gerir todas as sessões',
            'verTodasCompras' => 'Ver todas as compras',
            'confirmarBilhetes' => 'Confirmar bilhetes',
            'gerirAlugueres' => 'Gerir todos os alugueres',
            'verEstatisticas' => 'Ver estatísticas globais',
        ];

        foreach ($permissoes as $nome => $descricao) {
            $permissao = $auth->createPermission($nome);
            $permissao->description = $descricao;
            $auth->add($permissao);
            $$nome = $permissao;
        }

        // Associar permissões a rules
        $verPerfil->ruleName = $ownPerfilRule->name;
        $auth->update($verPerfil->name, $verPerfil);

        $editarPerfil->ruleName = $ownPerfilRule->name;
        $auth->update($editarPerfil->name, $editarPerfil);

        $eliminarPerfil->ruleName = $ownPerfilRule->name;
        $auth->update($eliminarPerfil->name, $eliminarPerfil);

        $verCompras->ruleName = $clienteRule->name;
        $auth->update($verCompras->name, $verCompras);

        $verAlugueres->ruleName = $clienteRule->name;
        $auth->update($verAlugueres->name, $verAlugueres);

        $eliminarAluguer->ruleName = $clienteRule->name;
        $auth->update($eliminarAluguer->name, $eliminarAluguer);

        // Permissões que dependem de um cinema
        $permissoesMatchCinema = [
            $verCinema,
            $verSalasCinema,
            $verSessoesCinema,
            $verComprasCinema,
            $confirmarBilhetesCinema,
            $verAlugueresCinema,
            $verEstatisticasCinema,
            $verFuncionariosCinema,
            $criarFuncionarioCinema,
            $alterarEstadoFuncionario,
            $gerirSalasCinema,
            $gerirSessoesCinema,
            $gerirAlugueresCinema,
            $editarCinema,
        ];

        foreach ($permissoesMatchCinema as $permissao) {
            $permissao->ruleName = $matchCinemaRule->name;
            $auth->update($permissao->name, $permissao);
        }

        // Cliente
        $cliente = $auth->createRole('cliente');
        $auth->add($cliente);
        $auth->addChild($cliente, $verPerfil);
        $auth->addChild($cliente, $editarPerfil);
        $auth->addChild($cliente, $eliminarPerfil);
        $auth->addChild($cliente, $criarCompra);
        $auth->addChild($cliente, $verCompras);
        $auth->addChild($cliente, $criarAluguer);
        $auth->addChild($cliente, $verAlugueres);
        $auth->addChild($cliente, $eliminarAluguer);


        // Funcionário
        $funcionario = $auth->createRole('funcionario');
        $auth->add($funcionario);
        $auth->addChild($funcionario, $verCinema);
        $auth->addChild($funcionario, $verSalasCinema);
        $auth->addChild($funcionario, $verSessoesCinema);
        $auth->addChild($funcionario, $verComprasCinema);
        $auth->addChild($funcionario, $confirmarBilhetesCinema);
        $auth->addChild($funcionario, $verAlugueresCinema);
        $auth->addChild($funcionario, $verEstatisticasCinema);

        $gerente = $auth->createRole('gerente');
        $auth->add($gerente);
        $auth->addChild($gerente, $funcionario);
        $auth->addChild($gerente, $verFuncionariosCinema);
        $auth->addChild($gerente, $criarFuncionarioCinema);
        $auth->addChild($gerente, $alterarEstadoFuncionario);
        $auth->addChild($gerente, $gerirSalasCinema);
        $auth->addChild($gerente, $gerirSessoesCinema);
        $auth->addChild($gerente, $gerirAlugueresCinema);
        $auth->addChild($gerente, $editarCinema);


        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $gerirUtilizadores);
        $auth->addChild($admin, $gerirCinemas);
        $auth->addChild($admin, $gerirSalas);
        $auth->addChild($admin, $gerirFilmes);
        $auth->addChild($admin, $gerirGeneros);
        $auth->addChild($admin, $gerirSessoes);
        $auth->addChild($admin, $verTodasCompras);
        $auth->addChild($funcionario, $confirmarBilhetes);
        $auth->addChild($admin, $gerirAlugueres);
        $auth->addChild($admin, $verEstatisticas);


        echo "RBAC inicializado com sucesso!\n";
    }
}
