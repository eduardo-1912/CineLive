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
            ['num_filas', 'compare', 'compareValue' => 26, 'operator' => '<=', 'type' => 'number', 'message' => 'O número máximo de filas permitido é 26.'],
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
            'precoEmEuros' => 'Preço Bilhete',
            'estado' => 'Estado',
            'estadoFormatado' => 'Estado',
        ];
    }

    // NOME DA SALA
    public function getNome()
    {
        return 'Sala ' . $this->numero;
    }

    // NÚMERO DE LUGARES
    public function getLugares()
    {
        return $this->num_filas * $this->num_colunas;
    }

    // PREÇO DO BILHETE EM EUROS
    public function getPrecoEmEuros()
    {
        return $this->preco_bilhete . '€';
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

    // OBTER SALAS DISPONÍVEIS
    public static function getSalasDisponiveis($cinemaId, $data, $horaInicio, $horaFim)
    {
        // SALAS COM SESSÕES SOBREPOSTAS NESSE HORÁRIO
        $salasOcupadas = Sessao::find()
            ->select('sala_id')
            ->where(['data' => $data])
            ->andWhere(['and',
                ['<', 'hora_inicio', $horaFim],
                ['>', 'hora_fim', $horaInicio],
            ])->column();

        // SALAS COM ALUGUERES CONFIRMADOS NESSE HORÁRIO
        $salasAlugadas = AluguerSala::find()
            ->select('sala_id')
            ->where(['data' => $data])
            ->andWhere(['estado' => [AluguerSala::ESTADO_CONFIRMADO, AluguerSala::ESTADO_A_DECORRER]])
            ->andWhere(['and',
                ['<', 'hora_inicio', $horaFim],
                ['>', 'hora_fim', $horaInicio],
            ])->column();

        // IDs DAS SALAS INDISPONÍVEIS
        $salasIndisponiveis = array_unique(array_merge($salasOcupadas, $salasAlugadas));

        // DEVOLVER APENAS AS SALAS ATIVAS E DISPONÍVEIS
        return self::find()
            ->where(['cinema_id' => $cinemaId, 'estado' => self::ESTADO_ATIVA])
            ->andFilterWhere(['not in', 'id', $salasIndisponiveis])
            ->orderBy('numero')
            ->all();
    }

    // VERIFICAR SE PODE SER EDITADA
    public function isEditable(): bool { return true; }

    // VERIFICAR SE PODE SER ATIVADA
    public function isActivatable(): bool
    {
        return $this->estado === self::ESTADO_ENCERRADA;
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
