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
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            ['username', 'unique'],
            ['email', 'unique'],

            // Campos extra
            ['role', 'safe'],
            ['password', 'string', 'min' => 8],
        ]);
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'password' => 'Palavra-passe',
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