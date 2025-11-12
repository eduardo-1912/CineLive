<?php

namespace frontend\models;

use common\models\UserProfile;
use Exception;
use Yii;
use yii\base\Model;
use common\models\User;
use yii\debug\models\search\Profile;


/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $nome;
    public $email;
    public $password;
    public $telemovel;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'telemovel', 'nome'], 'required'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'targetClass' => 'User', 'message' => 'Este nome de utilizador já está registado.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => 'User', 'message' => 'Este email já está registado.'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['telemovel', 'match', 'pattern' => '/^[0-9]{9}$/', 'message' => 'O telemóvel deve conter exatamente 9 dígitos.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'telemovel' => 'Telemóvel',
        ];
    }

    // CRIAR CONTA
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        // INICIAR TRANSACTION (TER A CERTEZA QUE NENHUM USER É CRIADO SEM USER_PROFILE)
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // CRIAR UTILIZADOR
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;

            if (!$user->save()) {
                throw new \Exception('Ocorreu um erro ao criar o utilizador: ' . json_encode($user->getErrors()));
            }

            // ROLE CLIENTE
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('cliente');
            if ($role) {
                $auth->assign($role, $user->id);
            }

            // CRIAR USER_PROFILE
            $profile = new UserProfile();
            $profile->user_id = $user->id;
            $profile->nome = $this->nome;
            $profile->telemovel = $this->telemovel;

            if (!$profile->save()) {
                throw new \Exception('Ocorreu um erro ao criar o perfil: ' . json_encode($profile->getErrors()));
            }

            // DAR COMMIT NA TRANSACTION
            $transaction->commit();

            return $user;
        }
        catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return null;
        }
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
