<?php

namespace common\models;

use Yii;
use yii\db\Expression;

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
            ['num_filas', 'max' => 26],
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
            'preco_bilhete' => 'Preço Bilhete',
            'estado' => 'Estado',
            'estadoFormatado' => 'Estado',
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

    public static function findDisponiveis($cinemaId, $data, $horaInicio, $horaFim, $salaAtualId = null): array
    {
        $salasOcupadas = Sessao::find()
            ->select('id')
            ->where(['data' => $data])
            ->andWhere(['and', ['<', 'hora_inicio', $horaFim], ['>', 'hora_fim', $horaInicio]])
            ->column();

        $salasAlugadas = AluguerSala::find()
            ->select('id')
            ->where(['data' => $data])
            ->andWhere(['estado' => [
                AluguerSala::ESTADO_PENDENTE,
                AluguerSala::ESTADO_CONFIRMADO,
                AluguerSala::ESTADO_A_DECORRER]])
            ->andWhere(['and', ['<', 'hora_inicio', $horaFim], ['>', 'hora_fim', $horaInicio]])
            ->column();

        $salasIndisponiveis = array_unique(array_merge($salasOcupadas, $salasAlugadas));

        if ($salaAtualId) {
            $salasIndisponiveis = array_diff($salasIndisponiveis, [$salaAtualId]);
        }

        return self::find()
            ->where(['cinema_id' => $cinemaId, 'estado' => self::ESTADO_ATIVA])
            ->andFilterWhere(['not in', 'id', $salasIndisponiveis])
            ->orderBy(['numero' => SORT_ASC])
            ->all();
    }


    // OBTER ESTADO FORMATADO
    public function getEstadoFormatado(): string
    {
        $labels = self::optsEstado();
        $label = $labels[$this->estado] ?? '-';

        $colors = [
            self::ESTADO_ATIVA => '',
            self::ESTADO_ENCERRADA => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    // VERIFICAR SE TEM SESSÕES ATIVAS
    public function hasSessoesAtivas(): bool
    {
        foreach ($this->sessaos as $sessao)
        {
            // IGNORAR SESSÕES TERMINADAS
            if ($sessao->isEstadoTerminada()) {
                continue;
            }

            // SE TEM SESSÕES ATIVAS
            if (!$sessao->isDeletable())
            {
                return true;
            }
        }

        // SE NENHUMA SESSÃO IMPEDE O ENCERRAMENTO DA SALA
        return false;
    }

    // VERIFICAR SE TEM ALUGUERES ATIVOS
    public function hasAlugueresAtivos(): bool
    {
        return $this->getAluguerSalas()
            ->where(['estado' => [
                AluguerSala::ESTADO_A_DECORRER,
                AluguerSala::ESTADO_CONFIRMADO,
            ]])
            ->exists();
    }




    // OBTER O PRÓXIMO NÚMERO INDICATIVO AO CRIAR UMA SALA NOVA
    public static function getProximoNumeroPorCinema($cinemaId): int
    {
        if (!$cinemaId) {
            return 1;
        }

        $ultimoNumero = self::find()
            ->where(['cinema_id' => $cinemaId])
            ->max('numero');

        return ($ultimoNumero ?? 0) + 1;
    }

    // VERIFICAR SE PODE SER EDITADA
    public function isEditable(): bool { return true; }

    // VERIFICAR SE PODE SER ATIVADA
    public function isActivatable(): bool
    {
        return $this->estado === self::ESTADO_ENCERRADA && $this->cinema->isEstadoAtivo();
    }

    // VERIFICAR SE PODE SER ENCERRADA
    public function isClosable(): bool
    {
        return !$this->hasSessoesAtivas() && !$this->hasAlugueresAtivos();
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
