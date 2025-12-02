<?php

namespace common\models;

/**
 * This is the model class for table "compra".
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $sessao_id
 * @property string $data
 * @property string $pagamento
 * @property string $lugares
 * @property string $estado
 *
 * @property-read $nome
 * @property-read float $total
 *
 * @property Bilhete[] $bilhetes
 * @property Sessao $sessao
 * @property User $cliente
 */
class Compra extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const PAGAMENTO_MBWAY = 'mbway';
    const PAGAMENTO_CARTAO = 'cartao';
    const PAGAMENTO_MULTIBANCO = 'multibanco';
    const ESTADO_CONFIRMADA = 'confirmada';
    const ESTADO_CANCELADA = 'cancelada';

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
            [['cliente_id', 'sessao_id', 'pagamento', 'estado'], 'required'],
            [['cliente_id', 'sessao_id'], 'integer'],
            [['data'], 'safe'],
            [['pagamento', 'estado'], 'string'],
            ['pagamento', 'in', 'range' => array_keys(self::optsPagamento())],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
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
            'cliente_id' => 'Cliente',
            'sessao_id' => 'Sessão',
            'data' => 'Data de Compra',
            'pagamento' => 'Pagamento',
            'estado' => 'Estado',
            'nomeCinema' => 'Cinema',
            'numeroBilhetes' => 'Bilhetes',
            'sessao' => 'Sessão',
            'lugares' => 'Lugares',
            'total' => 'Total',
        ];
    }

    public function getNome(): string
    {
        return "Compra #{$this->id}";
    }

    public function getNumeroBilhetes(): int
    {
        return count($this->bilhetes);
    }

    public function getLugares(): string
    {
        return implode(', ', array_column($this->bilhetes, 'lugar'));
    }

    public function getTotal(): float
    {
        return $this->getBilhetes()->sum('preco') ?? 0;
    }

    public function isTodosBilhetesConfirmados(): bool
    {
        return !$this->getBilhetes()->andWhere(['!=', 'estado', Bilhete::ESTADO_CONFIRMADO])->exists();
    }

    public function getEstadoHtml(): string
    {
        $label = $this->displayEstado() ?? '-';

        $colors = [
            self::ESTADO_CONFIRMADA => '',
            self::ESTADO_CANCELADA => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    public function getBilhetes()
    {
        return $this->hasMany(Bilhete::class, ['compra_id' => 'id']);
    }

    /**
     * Gets query for [[Sessão]].
     *
     * @return \yii\db\ActiveQuery
     */
     public function getSessao()
    {
        return $this->hasOne(Sessao::class, ['id' => 'sessao_id']);
    }

    /**
     * Gets query for [[Cinema]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id'])->via('sessao');
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
     * column pagamento ENUM value labels
     * @return string[]
     */
    public static function optsPagamento()
    {
        return [
            self::PAGAMENTO_MBWAY => 'MBWAY',
            self::PAGAMENTO_CARTAO => 'Cartão',
            self::PAGAMENTO_MULTIBANCO => 'Multibanco',
        ];
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_CONFIRMADA => 'Confirmada',
            self::ESTADO_CANCELADA => 'Cancelada',
        ];
    }

    /**
     * @return string
     */
    public function displayPagamento()
    {
        return self::optsPagamento()[$this->pagamento];
    }

    /**
     * @return bool
     */
    public function isPagamentoMbway()
    {
        return $this->pagamento === self::PAGAMENTO_MBWAY;
    }

    public function setPagamentoToMbway()
    {
        $this->pagamento = self::PAGAMENTO_MBWAY;
    }

    /**
     * @return bool
     */
    public function isPagamentoCartao()
    {
        return $this->pagamento === self::PAGAMENTO_CARTAO;
    }

    public function setPagamentoToCartao()
    {
        $this->pagamento = self::PAGAMENTO_CARTAO;
    }

    /**
     * @return bool
     */
    public function isPagamentoMultibanco()
    {
        return $this->pagamento === self::PAGAMENTO_MULTIBANCO;
    }

    public function setPagamentoToMultibanco()
    {
        $this->pagamento = self::PAGAMENTO_MULTIBANCO;
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
    public function isEstadoConfirmada()
    {
        return $this->estado === self::ESTADO_CONFIRMADA;
    }

    public function setEstadoToConfirmada()
    {
        $this->estado = self::ESTADO_CONFIRMADA;
    }

    /**
     * @return bool
     */
    public function isEstadoCancelada()
    {
        return $this->estado === self::ESTADO_CANCELADA;
    }

    public function setEstadoToCancelada()
    {
        $this->estado = self::ESTADO_CANCELADA;
    }
}
