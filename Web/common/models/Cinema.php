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
 * @property string $estado
 * @property int|null $gerente_id
 *
 * @property User $gerente
 * @property Sala[] $salas
 * @property Sessao[] $sessaos
 * @property UserProfile[] $userProfiles
 */
class Cinema extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_ATIVO = 'ativo';
    const ESTADO_ENCERRADO = 'encerrado';

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
            [['gerente_id'], 'default', 'value' => null],
            [['nome', 'rua', 'codigo_postal', 'cidade', 'latitude', 'longitude', 'email', 'telefone', 'horario_abertura', 'horario_fecho', 'estado'], 'required'],
            [['latitude', 'longitude'], 'number'],
            [['telefone', 'gerente_id'], 'integer'],
            [['horario_abertura', 'horario_fecho'], 'safe'],
            [['estado'], 'string'],
            [['nome'], 'string', 'max' => 80],
            [['rua'], 'string', 'max' => 100],
            [['codigo_postal'], 'string', 'max' => 8],
            [['cidade'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 255],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
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
            'codigo_postal' => 'Código Postal',
            'cidade' => 'Cidade',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'horario_abertura' => 'Hórario de Abertura',
            'horario_fecho' => 'Hórario de Fecho',
            'estado' => 'Estado',
            'gerente_id' => 'Gerente',
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
     * Gets query for [[Sessaos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessaos()
    {
        return $this->hasMany(Sessao::class, ['cinema_id' => 'id']);
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


    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVO => 'ativo',
            self::ESTADO_ENCERRADO => 'encerrado',
        ];
    }

    /**
     * @return string
     */
    public function displayEstado()
    {
        return self::optsEstado()[$this->estado];
    }

    /**
     * @return bool
     */
    public function isEstadoAtivo()
    {
        return $this->estado === self::ESTADO_ATIVO;
    }

    public function setEstadoToAtivo()
    {
        $this->estado = self::ESTADO_ATIVO;
    }

    /**
     * @return bool
     */
    public function isEstadoEncerrado()
    {
        return $this->estado === self::ESTADO_ENCERRADO;
    }

    public function setEstadoToEncerrado()
    {
        $this->estado = self::ESTADO_ENCERRADO;
    }
}
