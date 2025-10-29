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


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cinema_id', 'nome', 'telemovel'], 'default', 'value' => null],
            [['user_id'], 'required'],
            [['user_id', 'cinema_id'], 'integer'],
            [['nome'], 'string', 'max' => 100],
            [['telemovel'], 'string', 'max' => 9],
            [['user_id'], 'unique'],
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

            // Campo cinema_id obrigatório para gerente ou funcionário
            [
                'cinema_id',
                'required',
                'when' => function ($model) {
                    if (!$model->user_id) return false; // ainda não associado
                    $roles = Yii::$app->authManager->getRolesByUser($model->user_id);
                    return isset($roles['gerente']) || isset($roles['funcionario']);
                },
                'whenClient' => "function (attribute, value) {
                let role = $('#userextension-role').val();
                return role === 'gerente' || role === 'funcionario';
            }",
                'message' => 'O campo Cinema é obrigatório para gerentes e funcionários.',
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'cinema_id' => 'Cinema ID',
            'nome' => 'Nome',
            'telemovel' => 'Telemovel',
        ];
    }

    /**
     * Gets query for [[Cinema]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
