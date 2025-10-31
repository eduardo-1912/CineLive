<?php

namespace common\models;

use Yii;

class UserExtension extends User
{
    // Campos virtuais
    public $role;
    public $password;
    public $cinema_id;

    /**
     * Regras de validação
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            // Campos de conta
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            [['username', 'email'], 'unique'],
            ['role', 'safe'],

            // Password obrigatória apenas no create
            ['password', 'string', 'min' => 8],
            [
                'password',
                'required',
                'when' => fn($model) => $model->isNewRecord,
                'whenClient' => "function() {
                    return !$('#userextension-id').val();
                }",
                'message' => 'Password cannot be blank.',
            ],
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

    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id'])->via('profile');
    }

    public function getRoleName()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        if (empty($roles)) return null;

        return array_key_first($roles);
    }

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
