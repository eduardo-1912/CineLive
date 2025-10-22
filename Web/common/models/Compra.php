<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "compra".
 *
 * @property int $id
 * @property int $cliente_id
 * @property string $data
 * @property string $estado_pagamento
 *
 * @property Bilhete[] $bilhetes
 * @property User $cliente
 */
class Compra extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_PAGAMENTO_PENDENTE = 'pendente';
    const ESTADO_PAGAMENTO_CONFIRMADO = 'confirmado';
    const ESTADO_PAGAMENTO_CANCELADO = 'cancelado';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'compra';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'estado_pagamento'], 'required'],
            [['cliente_id'], 'integer'],
            [['data'], 'safe'],
            [['estado_pagamento'], 'string'],
            ['estado_pagamento', 'in', 'range' => array_keys(self::optsEstadoPagamento())],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['cliente_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cliente_id' => 'Cliente ID',
            'data' => 'Data',
            'estado_pagamento' => 'Estado Pagamento',
        ];
    }

    /**
     * Gets query for [[Bilhetes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBilhetes()
    {
        return $this->hasMany(Bilhete::class, ['compra_id' => 'id']);
    }

    /**
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCliente()
    {
        return $this->hasOne(User::class, ['id' => 'cliente_id']);
    }


    /**
     * column estado_pagamento ENUM value labels
     * @return string[]
     */
    public static function optsEstadoPagamento()
    {
        return [
            self::ESTADO_PAGAMENTO_PENDENTE => 'pendente',
            self::ESTADO_PAGAMENTO_CONFIRMADO => 'confirmado',
            self::ESTADO_PAGAMENTO_CANCELADO => 'cancelado',
        ];
    }

    /**
     * @return string
     */
    public function displayEstadoPagamento()
    {
        return self::optsEstadoPagamento()[$this->estado_pagamento];
    }

    /**
     * @return bool
     */
    public function isEstadoPagamentoPendente()
    {
        return $this->estado_pagamento === self::ESTADO_PAGAMENTO_PENDENTE;
    }

    public function setEstadoPagamentoToPendente()
    {
        $this->estado_pagamento = self::ESTADO_PAGAMENTO_PENDENTE;
    }

    /**
     * @return bool
     */
    public function isEstadoPagamentoConfirmado()
    {
        return $this->estado_pagamento === self::ESTADO_PAGAMENTO_CONFIRMADO;
    }

    public function setEstadoPagamentoToConfirmado()
    {
        $this->estado_pagamento = self::ESTADO_PAGAMENTO_CONFIRMADO;
    }

    /**
     * @return bool
     */
    public function isEstadoPagamentoCancelado()
    {
        return $this->estado_pagamento === self::ESTADO_PAGAMENTO_CANCELADO;
    }

    public function setEstadoPagamentoToCancelado()
    {
        $this->estado_pagamento = self::ESTADO_PAGAMENTO_CANCELADO;
    }
}
