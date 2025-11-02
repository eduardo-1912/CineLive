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
            ['telemovel', 'match', 'pattern' => '/^[0-9]{9}$/', 'message' => 'O telemóvel deve conter exatamente 9 dígitos.'],
            [['user_id'], 'unique'],
            [['cinema_id'], 'default', 'value' => null],

            // VERIFICAR QUE O ID DO CINEMA É VÁLIDO
            [
                ['cinema_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Cinema::class,
                'targetAttribute' => ['cinema_id' => 'id']
            ],

            // VERIFICAR QUE O ID DO USER É VÁLIDO
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
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

    // OBTER O UTILIZADOR
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // OBTER O CINEMA DO UTILIZADOR
    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id']);
    }
}
