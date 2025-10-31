<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_profile".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $cinema_id
 * @property string|null $nome
 * @property string|null $telemovel
 *
 * @property Cinema $cinema
 * @property User $user
 */
class UserProfile extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'user_profile';
    }

    public function rules()
    {
        return [
            [['user_id', 'nome', 'telemovel'], 'required'],
            [['user_id', 'cinema_id'], 'integer'],
            [['nome'], 'string', 'max' => 100],
            [['telemovel'], 'string', 'max' => 9],
            [['user_id'], 'unique'],
            [['cinema_id'], 'default', 'value' => null],
            [
                ['cinema_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Cinema::class,
                'targetAttribute' => ['cinema_id' => 'id']
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],

            // Cinema obrigatório para gerente/funcionário
            [
                'cinema_id',
                'required',
                'when' => fn($model) => $model->isStaff(),
                'whenClient' => "function() {
                    const role = $('#userextension-role').val();
                    return role === 'gerente' || role === 'funcionario';
                }",
                'message' => 'O campo Cinema é obrigatório para Gerentes e Funcionários.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'cinema_id' => 'Cinema',
            'nome' => 'Nome',
            'telemovel' => 'Telemóvel',
        ];
    }

    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // Verifica se o utilizador é gerente ou funcionário
    public function isStaff()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        return isset($roles['gerente']) || isset($roles['funcionario']);
    }
}
