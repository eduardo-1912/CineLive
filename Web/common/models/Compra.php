<?php

namespace common\models;

use common\components\EmailHelper;

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
 * @property-read string $estadoFormatado
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

    // VERIFICAR SE TODOS OS BILHETES ESTÃO CONFIRMADOS
    public function isTodosBilhetesConfirmados(): bool
    {
        return !$this->getBilhetes()
            ->andWhere(['!=', 'estado', Bilhete::ESTADO_CONFIRMADO])
            ->exists();
    }


    // OBTER ESTADO FORMATADO
    public function getEstadoFormatado(): string
    {
        $labels = self::optsEstado();
        $label = $labels[$this->estado] ?? 'Desconhecida';

        $colors = [
            self::ESTADO_CONFIRMADA => '',
            self::ESTADO_CANCELADA => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    public function getPagamentoFormatado(): string
    {
        return self::optsPagamento()[$this->pagamento] ?? ucfirst($this->pagamento);
    }


    // TODO: COMMNETS
    public function getBilhetes()
    {
        return $this->hasMany(Bilhete::class, ['compra_id' => 'id']);
    }

    public function enviarEmailEstado($estadoNovo)
    {
        // GARANTIR QUE EXISTE CLIENTE
        if (!$this->cliente || !$this->cliente->email) {
            return false;
        }

        // DADOS DO CLIENTE
        $nome = $this->cliente->profile->nome ?? $this->cliente->username;
        $email = $this->cliente->email;

        // NOME DO CINEMA
        $cinemaNome = $this->sessao->cinema->nome ?? 'CineLive';

        // TEXTO DEPENDENTE DO ESTADO
        switch ($estadoNovo) {
            case self::ESTADO_CONFIRMADA:
                $titulo = 'Confirmação da sua compra - CineLive';
                $mensagem = "
                <p>Olá <strong>{$nome}</strong>,</p>
                <p>A sua <b>compra #{$this->id}</b> foi <span style='color:green;'>confirmada</span> com sucesso.</p>
                <p>Os seus bilhetes estão disponíveis na sua área de cliente.</p>
                <p style='margin-top:0.75rem;'>Cinema: <b>{$cinemaNome}</b></p>
                <p style='margin-top:0.75rem;'>Cumprimentos,<br><b>Equipa CineLive</b></p>";
                break;

            case self::ESTADO_CANCELADA:
                $titulo = 'Cancelamento da sua compra - CineLive';
                $mensagem = "
                <p>Olá <strong>{$nome}</strong>,</p>
                <p>Lamentamos informar que a sua <b>compra #{$this->id}</b> foi <span style='color:#c00;'>cancelada</span>.</p>
                <p>Se acha que isto foi um erro, por favor contacte o cinema correspondente.</p>
                <p style='margin-top:0.75rem;'>Cumprimentos,<br><b>Equipa CineLive</b></p>";
                break;

            default:
                return false;
        }

        // ENVIAR EMAIL
        return EmailHelper::enviarEmail($email, $titulo, $mensagem);
    }

    // OBTER SESSÕES
     public function getSessao()
    {
        return $this->hasOne(Sessao::class, ['id' => 'sessao_id']);
    }


    // OBTER CINEMAS
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
