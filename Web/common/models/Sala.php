<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sala".
 *
 * @property int $id
 * @property int $cinema_id
 * @property int $numero
 * @property int $num_filas
 * @property int $num_colunas
 * @property float $preco_bilhete
 * @property string $estado
 *
 * @property AluguerSala[] $aluguerSalas
 * @property Cinema $cinema
 * @property Sessao[] $sessaos
 */
class Sala extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_ATIVA = 'ativa';
    const ESTADO_ENCERRADA = 'encerrada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sala';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cinema_id', 'numero', 'num_filas', 'num_colunas', 'preco_bilhete', 'estado'], 'required'],
            [['cinema_id', 'numero', 'num_filas', 'num_colunas'], 'integer'],
            [['preco_bilhete'], 'number'],
            [['estado'], 'string'],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['cinema_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cinema::class, 'targetAttribute' => ['cinema_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cinema_id' => 'Cinema ID',
            'numero' => 'Numero',
            'num_filas' => 'Num Filas',
            'num_colunas' => 'Num Colunas',
            'preco_bilhete' => 'Preco Bilhete',
            'estado' => 'Estado',
        ];
    }

    /**
     * Gets query for [[AluguerSalas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAluguerSalas()
    {
        return $this->hasMany(AluguerSala::class, ['sala_id' => 'id']);
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
     * Gets query for [[Sessaos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessaos()
    {
        return $this->hasMany(Sessao::class, ['sala_id' => 'id']);
    }


    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVA => 'ativa',
            self::ESTADO_ENCERRADA => 'encerrada',
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
    public function isEstadoAtiva()
    {
        return $this->estado === self::ESTADO_ATIVA;
    }

    public function setEstadoToAtiva()
    {
        $this->estado = self::ESTADO_ATIVA;
    }

    /**
     * @return bool
     */
    public function isEstadoEncerrada()
    {
        return $this->estado === self::ESTADO_ENCERRADA;
    }

    public function setEstadoToEncerrada()
    {
        $this->estado = self::ESTADO_ENCERRADA;
    }
}
