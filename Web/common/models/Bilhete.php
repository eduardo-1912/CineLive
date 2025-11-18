<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bilhete".
 *
 * @property int $id
 * @property int $compra_id
 * @property string $lugar
 * @property float $preco
 * @property string $codigo
 * @property string $estado
 *
 * @property Compra $compra
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
            [['compra_id', 'lugar', 'preco', 'codigo', 'estado'], 'required'],
            [['compra_id'], 'integer'],
            [['preco'], 'number'],
            [['estado'], 'string'],
            [['lugar'], 'string', 'max' => 3],
            [['codigo'], 'string', 'max' => 45],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['codigo'], 'unique'],
            [['compra_id'], 'exist', 'skipOnError' => true, 'targetClass' => Compra::class, 'targetAttribute' => ['compra_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'compra_id' => 'Compra',
            'lugar' => 'Lugar',
            'preco', 'precoEmEuros' => 'Preço',
            'codigo' => 'Código',
            'estado' => 'Estado',
        ];
    }

    public function getPrecoEmEuros(): string
    {
        return number_format($this->preco, 2) . '€';
    }

    public function getEstadoFormatado(): string
    {
        $label = self::optsEstado()[$this->estado] ?? '-';

        $colors = [
            self::ESTADO_CONFIRMADO => '',
            self::ESTADO_PENDENTE => 'text-secondary',
            self::ESTADO_CANCELADO => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    public static function gerarCodigo()
    {
        do {
            $codigo = strtoupper(Yii::$app->security->generateRandomString(6));
        } while (self::find()->where(['codigo' => $codigo])->exists());

        return $codigo;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->isEstadoPendente() && !$this->compra->sessao->isEstadoTerminada();
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
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_PENDENTE => 'Pendente',
            self::ESTADO_CONFIRMADO => 'Confirmado',
            self::ESTADO_CANCELADO => 'Cancelado',
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
