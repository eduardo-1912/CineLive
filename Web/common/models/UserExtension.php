<?php

namespace common\models;

use Yii;

/*
 * O modelo UserExtension foi criado porque não foi gerado o modelo User outra vez.
 * Objetivo de não alterar nada no User (não é upgrade-safe).
 * Existe para fazer a ligação entre User e UserProfile com regras para Roles e Cinema.
 * Foi definido como modelo default do 'Yii::$app->user' para poder aceder facilmente ao Role e Cinema.
*/

class UserExtension extends User
{
    // CAMPOS TEMPORÁRIOS
    public $password;
    public $role;
    public $cinema_id;

    /**
     * Regras de validação
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            // CAMPOS DA CONTA
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            [['username', 'email'], 'unique'],
            ['role', 'safe'],

            // PASSWORD OBRIGATÓRIA (APENAS NO CREATE)
            ['password', 'string', 'min' => 8],
            [
                'password',
                'required',
                'when' => fn($model) => $model->isNewRecord,
                'whenClient' => "function() {
                
                    // DEVOLVER TRUE QUANDO NÃO HÁ ID (OU SEJA, QUANDO O UTILIZADOR AINDA NÃO EXISTE)
                    return !$('#userextension-id').val();
                    
                }",
                'message' => 'Password cannot be blank.',
            ],
        ]);
    }

    // GUARDAR A PASSWORD E AUTH_KEY/TOKEN
    public function beforeSave($insert)
    {
        if ($this->password) {
            $this->setPassword($this->password);
        }

        if ($insert) {
            if (empty($this->auth_key)) {
                $this->generateAuthKey();
            }
            if (empty($this->verification_token)) {
                $this->generateEmailVerificationToken();
            }
        }

        return parent::beforeSave($insert);
    }

    // OBTER USER_PROFILE DO USER
    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    // OBTER CINEMA DO USER
    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id'])->via('profile');
    }

    // OBTER ROLE DO USER
    public function getRoleName()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        if (empty($roles)) return null;

        return array_key_first($roles);
    }

    // OBTER ROLE DO USER (FORMATADO)
    public function getRoleFormatted()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        if (empty($roles)) return '-';

        $roleKey = array_key_first($roles);
        $labels = [
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'funcionario' => 'Funcionário',
            'cliente' => 'Cliente',
        ];

        return $labels[$roleKey] ?? ucfirst($roleKey);
    }
}
