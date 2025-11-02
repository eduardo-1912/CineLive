<?php

namespace console\controllers;

use common\models\User;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        //PERMISSÕES
        $permissoes = [
            'gerirUtilizadores' => 'Gerir utilizadores e perfis',
            'gerirCinemas' => 'Gerir cinemas',
            'gerirSalas' => 'Gerir salas de cinema',
            'gerirFilmes' => 'Gerir filmes e géneros',
            'gerirSessoes' => 'Gerir sessões de filmes',
            'gerirCompras' => 'Gerir bilhetes vendidos e cancelados',
            'verRelatorios' => 'Consultar estatísticas e relatórios de gestão',
            'gerirFuncionarios' => 'Gerir funcionários do cinema',
            'gerirAlugueres' => 'Gerir os pedidos de aluguer recebidos',
            'validarBilhetes' => 'Validar bilhetes na entrada das sessões',
            'consultarBilhetes' => 'Consultar bilhetes emitidos',
            'comprarBilhetes' => 'Comprar bilhetes através da app ou site',
            'editarPerfil' => 'Editar dados pessoais do perfil',
            'verHistorico' => 'Ver histórico de compras e alugueres',
            'alugarSala' => 'Solicitar aluguer de uma sala',
        ];

        foreach ($permissoes as $nome => $descricao) {
            $perm = $auth->createPermission($nome);
            $perm->description = $descricao;
            $auth->add($perm);
            $$nome = $perm;
        }

        // ROLES
        $cliente = $auth->createRole('cliente');
        $auth->add($cliente);
        $auth->addChild($cliente, $comprarBilhetes);
        $auth->addChild($cliente, $verHistorico);
        $auth->addChild($cliente, $editarPerfil);
        $auth->addChild($cliente, $alugarSala);

        $funcionario = $auth->createRole('funcionario');
        $auth->add($funcionario);
        $auth->addChild($funcionario, $consultarBilhetes);
        $auth->addChild($funcionario, $validarBilhetes);

        $gerente = $auth->createRole('gerente');
        $auth->add($gerente);
        $auth->addChild($gerente, $gerirSessoes);
        $auth->addChild($gerente, $gerirFuncionarios);
        $auth->addChild($gerente, $gerirCompras);
        $auth->addChild($gerente, $gerirAlugueres);
        $auth->addChild($gerente, $funcionario);
        $auth->addChild($gerente, $gerirSalas);
        $auth->addChild($gerente, $verRelatorios);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $gerirUtilizadores);
        $auth->addChild($admin, $gerirCinemas);
        $auth->addChild($admin, $gerirFilmes);
        $auth->addChild($admin, $gerente);

        //CRIAR UTILIZADOR ADMIN
        $userClass = User::class;

        if ($userClass::find()->where(['username' => 'admin'])->exists()) {
            echo "O utilizador admin já existe... a saltar criação.\n";
        }
        else {
            $adminUser = new $userClass();
            $adminUser->username = 'admin';
            $adminUser->email = 'admin@cinelive.pt';
            $adminUser->setPassword('admin123');
            $adminUser->generateAuthKey();
            $adminUser->status = 10;
            $adminUser->created_at = time();
            $adminUser->updated_at = time();

            if ($adminUser->save()) {
                $auth->assign($admin, $adminUser->id);
                echo "Utilizador admin criado com sucesso (login: admin/admin123)\n";
            }
            else {
                echo "Erro ao criar o utilizador admin:\n";
                print_r($adminUser->errors);
            }
        }

        echo "RBAC inicializado com sucesso!\n";
    }

    public function actionAssign($roleName, $userId)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if (!$role) {
            echo "Role '{$roleName}' não existe.\n";
            return;
        }

        $auth->assign($role, $userId);
        echo "Role '{$roleName}' atribuído ao utilizador com ID {$userId}.\n";
    }
}
