<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cinema".
 *
 * @property int $id
 * @property string $nome
 * @property string $rua
 * @property string $codigo_postal
 * @property string $cidade
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $email
 * @property int $telefone
 * @property string $horario_abertura
 * @property string $horario_fecho
 * @property int|null $gerente_id
 *
 * @property User $gerente
 * @property Sala[] $salas
 * @property UserProfile[] $userProfiles
 */
class Cinema extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cinema';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['latitude', 'longitude', 'gerente_id'], 'default', 'value' => null],
            [['nome', 'rua', 'codigo_postal', 'cidade', 'email', 'telefone', 'horario_abertura', 'horario_fecho'], 'required'],
            [['latitude', 'longitude'], 'number'],
            [['telefone', 'gerente_id'], 'integer'],
            [['horario_abertura', 'horario_fecho'], 'safe'],
            [['nome'], 'string', 'max' => 80],
            [['rua'], 'string', 'max' => 100],
            [['codigo_postal'], 'string', 'max' => 8],
            [['cidade'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 255],
            [['gerente_id'], 'unique'],
            [['gerente_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['gerente_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'rua' => 'Rua',
            'codigo_postal' => 'Codigo Postal',
            'cidade' => 'Cidade',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'horario_abertura' => 'Horario Abertura',
            'horario_fecho' => 'Horario Fecho',
            'gerente_id' => 'Gerente ID',
        ];
    }

    /**
     * Gets query for [[Gerente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGerente()
    {
        return $this->hasOne(User::class, ['id' => 'gerente_id']);
    }

    /**
     * Gets query for [[Salas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['cinema_id' => 'id']);
    }

    /**
     * Gets query for [[UserProfiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfiles()
    {
        return $this->hasMany(UserProfile::class, ['cinema_id' => 'id']);
    }

}
