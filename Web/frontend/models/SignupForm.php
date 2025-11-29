<?php

namespace frontend\models;

use common\models\UserProfile;
use Exception;
use Yii;
use yii\base\Model;
use common\models\User;


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
            ['username', 'unique', 'targetClass' => User::class],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            [['telemovel'], 'string', 'min' => 9, 'max' => 9],
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

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        if (!$user->save()) {
            throw new Exception('Ocorreu um erro ao criar o utilizador');
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole('cliente');
        if ($role) {$auth->assign($role, $user->id);}

        $profile = new UserProfile();
        $profile->user_id = $user->id;
        $profile->nome = $this->nome;
        $profile->telemovel = $this->telemovel;

        if (!$profile->save()) {
            throw new Exception('Ocorreu um erro ao criar o perfil');
        }

        return $user;
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