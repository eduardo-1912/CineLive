<?php

namespace common\models;

class UserExtension extends User
{
    // Propriedade temporária para guardar o role do RBAC
    public $role;

    // Propriedade temporária para guardar o password de um User Novo
    public $password;

    public function rules()
    {
        return array_merge(parent::rules(), [
            // Campos de conta
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['password', 'string', 'min' => 8],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            [['username', 'email'], 'unique'],
            ['role', 'safe'],
        ]);
    }

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

    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }
}