<?php

namespace common\models;

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
 * @property-read string $nome
 * @property-read int $numeroLugares
 * @property-read array $lugares
 * @property-read string $estadoHtml
 *
 * @property AluguerSala[] $aluguerSalas
 * @property Cinema $cinema
 * @property Sessao[] $sessoes
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
            ['num_filas', 'integer', 'max' => 26],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cinema_id' => 'Cinema',
            'nome' => 'Nome',
            'numero' => 'Número',
            'num_filas' => 'Número Filas',
            'num_colunas' => 'Número Colunas',
            'numeroLugares' => 'Lugares',
            'preco_bilhete' => 'Preço Bilhete',
            'estado', 'estadoHtml' => 'Estado',
        ];
    }

    public function getNome(): string
    {
        return "Sala {$this->numero}";
    }

    public function getNumeroLugares(): int
    {
        return $this->num_filas * $this->num_colunas;
    }

    public function getLugares(): array
    {
        $lugares = [];
        for ($fila = 1; $fila <= $this->num_filas; $fila++) {
            for ($coluna = 1; $coluna <= $this->num_colunas; $coluna++) {
                $lugares[] = chr(64 + $fila) . $coluna;
            }
        }

        return $lugares;
    }

    public function getEstadoHtml(): string
    {
        $label = $this->displayEstado() ?? '-';

        $colors = [
            self::ESTADO_ATIVA => '',
            self::ESTADO_ENCERRADA => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    public function getSessoesValidas(): array
    {
        return array_filter($this->sessoes, fn($sessao)
            => $sessao->isEstadoAtiva()
            || $sessao->isEstadoADecorrer()
            || $sessao->isEstadoEsgotada());
    }

    public function getAlugueresValidos(): array
    {
        return array_filter($this->aluguerSalas, fn($aluguer)
        => $aluguer->isEstadoPendente()
            || $aluguer->isEstadoConfirmado()
            || $aluguer->isEstadoADecorrer());
    }

    public function isEditable(): bool {
        return $this->cinema->isEstadoAtivo();
    }

    public function isActivatable(): bool
    {
        return $this->estado === self::ESTADO_ENCERRADA
            && $this->cinema->isEstadoAtivo();
    }

    public function isClosable(): bool
    {
        return $this->estado === self::ESTADO_ATIVA
            && empty($this->getSessoesValidas())
            && empty($this->getAlugueresValidos());
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
     * Gets query for [[Sessões]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessoes()
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
            self::ESTADO_ATIVA => 'Ativa',
            self::ESTADO_ENCERRADA => 'Encerrada',
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
