<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bilhete".
 *
 * @property int $id
 * @property int $compra_id
 * @property int $sessao_id
 * @property string $lugar
 * @property float $preco
 * @property string $codigo
 * @property string $estado
 *
 * @property Compra $compra
 * @property Sessao $sessao
 */
class Bilhete extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_PENDENTE = 'pendente';
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_CANCELADO = 'cancelado';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bilhete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['compra_id', 'sessao_id', 'lugar', 'preco', 'codigo', 'estado'], 'required'],
            [['compra_id', 'sessao_id'], 'integer'],
            [['preco'], 'number'],
            [['estado'], 'string'],
            [['lugar'], 'string', 'max' => 3],
            [['codigo'], 'string', 'max' => 45],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['codigo'], 'unique'],
            [['compra_id'], 'exist', 'skipOnError' => true, 'targetClass' => Compra::class, 'targetAttribute' => ['compra_id' => 'id']],
            [['sessao_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sessao::class, 'targetAttribute' => ['sessao_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'compra_id' => 'Compra ID',
            'sessao_id' => 'Sessao ID',
            'lugar' => 'Lugar',
            'preco' => 'Preco',
            'codigo' => 'Codigo',
            'estado' => 'Estado',
        ];
    }

    /**
     * Gets query for [[Compra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompra()
    {
        return $this->hasOne(Compra::class, ['id' => 'compra_id']);
    }

    /**
     * Gets query for [[Sessao]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessao()
    {
        return $this->hasOne(Sessao::class, ['id' => 'sessao_id']);
    }


    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_PENDENTE => 'pendente',
            self::ESTADO_CONFIRMADO => 'confirmado',
            self::ESTADO_CANCELADO => 'cancelado',
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
    public function isEstadoPendente()
    {
        return $this->estado === self::ESTADO_PENDENTE;
    }

    public function setEstadoToPendente()
    {
        $this->estado = self::ESTADO_PENDENTE;
    }

    /**
     * @return bool
     */
    public function isEstadoConfirmado()
    {
        return $this->estado === self::ESTADO_CONFIRMADO;
    }

    public function setEstadoToConfirmado()
    {
        $this->estado = self::ESTADO_CONFIRMADO;
    }

    /**
     * @return bool
     */
    public function isEstadoCancelado()
    {
        return $this->estado === self::ESTADO_CANCELADO;
    }

    public function setEstadoToCancelado()
    {
        $this->estado = self::ESTADO_CANCELADO;
    }
}
