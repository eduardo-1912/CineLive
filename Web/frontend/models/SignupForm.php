<?php

namespace frontend\models;

use common\models\UserProfile;
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

    // CRIAR CONTA
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        // CRIAR UTILIZADOR
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE; // CONTA ATIVA POR DEFAULT

        if ($user->save()) {

            // DAR ASSIGN DE ROLE 'CLIENTE'
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
                Yii::error($profile->errors, __METHOD__);
            }

            // DEVOLVE O USER EM VEZ DE TRUE/FALSE (DÁ PARA FAZER LOGIN AUTOMÁTICO)
            return $user;
        }
        return null;
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
