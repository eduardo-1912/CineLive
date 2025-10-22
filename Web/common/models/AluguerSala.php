<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "aluguer_sala".
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $sala_id
 * @property string $data
 * @property string $hora_inicio
 * @property string $hora_fim
 * @property string $estado
 * @property string $tipo_evento
 * @property string $observacoes
 *
 * @property User $cliente
 * @property Sala $sala
 */
class AluguerSala extends \yii\db\ActiveRecord
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
        return 'aluguer_sala';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'sala_id', 'data', 'hora_inicio', 'hora_fim', 'estado', 'tipo_evento', 'observacoes'], 'required'],
            [['cliente_id', 'sala_id'], 'integer'],
            [['data', 'hora_inicio', 'hora_fim'], 'safe'],
            [['estado', 'observacoes'], 'string'],
            [['tipo_evento'], 'string', 'max' => 100],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['cliente_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
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
            'sala_id' => 'Sala ID',
            'data' => 'Data',
            'hora_inicio' => 'Hora Inicio',
            'hora_fim' => 'Hora Fim',
            'estado' => 'Estado',
            'tipo_evento' => 'Tipo Evento',
            'observacoes' => 'Observacoes',
        ];
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
     * Gets query for [[Sala]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'sala_id']);
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
