<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\User;
use common\tests\UnitTester;
use Yii;

class UserTest extends Unit
{
    protected UnitTester $tester;

    protected function _before()
    {
        Yii::$app->params['user.passwordMinLength'] = 8;
    }

    private function createUser(string $username, string $role): User
    {
        $user = new User([
            'username' => $username,
            'email' => "$username@cinelive.pt",
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('12345678');
        $user->generateAuthKey();
        $user->save();

        Yii::$app->authManager->assign(Yii::$app->authManager->getRole($role), $user->id);

        return $user;
    }

    public function testCRUD()
    {
        // Create
        $user = $this->createUser('newuser', 'cliente');

        // Read
        $this->assertNotNull(User::findOne($user->id));

        // Update
        $user->email = 'editeduser@cinelive.pt';
        $this->assertTrue($user->save());
        $this->assertEquals('editeduser@cinelive.pt', User::findOne($user->id)->email);

        // Delete
        $user->delete();
        $this->assertNull(User::findOne($user->id));
    }


    public function testRoles()
    {
        $auth = Yii::$app->authManager;

        $this->assertNotNull($auth->getRole('cliente'));
        $this->assertNotNull($auth->getRole('funcionario'));
        $this->assertNotNull($auth->getRole('gerente'));
        $this->assertNotNull($auth->getRole('admin'));
    }

    public function testPermissoesAdmin()
    {
        $admin = $this->createUser('administrador', 'admin');

        $this->assertTrue(Yii::$app->authManager->checkAccess($admin->id, 'gerirUtilizadores'));
        $this->assertTrue(Yii::$app->authManager->checkAccess($admin->id, 'gerirFilmes'));
    }

    public function testPermissoesCliente()
    {
        $cliente = $this->createUser('cliente', 'cliente');

        Yii::$app->user->login($cliente);
        $this->assertTrue(Yii::$app->user->can('verPerfil', ['model' => $cliente]));
        $this->assertTrue(Yii::$app->user->can('editarPerfil', ['model' => $cliente]));
    }

    public function testClienteCantSeeOtherUser()
    {
        $user1 = $this->createUser('user1', 'cliente');
        $user2 = $this->createUser('user2', 'cliente');

        Yii::$app->user->login($user1);

        $this->assertFalse(Yii::$app->user->can('editarPerfil', ['model' => $user2]));
    }
}
