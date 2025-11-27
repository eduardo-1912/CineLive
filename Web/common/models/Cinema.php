<?php

namespace common\models;

use DateTime;
use Yii;

/**
 * This is the model class for table "cinema".
 *
 * @property int $id
 * @property string $nome
 * @property string $rua
 * @property string $codigo_postal
 * @property string $cidade
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $email
 * @property int $telefone
 * @property string $horario_abertura
 * @property string $horario_fecho
 * @property string $estado
 * @property int|null $gerente_id
 *
 * @property User $gerente
 * @property Sala[] $salas
 * @property Sessao[] $sessaos
 * @property UserProfile[] $userProfiles
 */
class Cinema extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_ATIVO = 'ativo';
    const ESTADO_ENCERRADO = 'encerrado';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cinema';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['gerente_id'], 'default', 'value' => null],
            [['nome', 'rua', 'codigo_postal', 'cidade', 'latitude', 'longitude', 'email', 'telefone', 'horario_abertura', 'horario_fecho', 'estado'], 'required'],
            [['latitude', 'longitude'], 'number'],
            [['telefone', 'gerente_id'], 'integer'],
            [['horario_abertura', 'horario_fecho'], 'safe'],
            [['estado'], 'string'],
            [['nome'], 'string', 'max' => 80],
            [['rua'], 'string', 'max' => 100],
            [['codigo_postal'], 'string', 'max' => 8],
            [['cidade'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 255],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['gerente_id'], 'unique'],
            [['gerente_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['gerente_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'rua' => 'Rua',
            'codigo_postal' => 'Código Postal',
            'cidade' => 'Cidade',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'horario_abertura' => 'Hórario de Abertura',
            'horario_fecho' => 'Hórario de Fecho',
            'horario' => 'Horário',
            'estado' => 'Estado',
            'gerente_id' => 'Gerente',
        ];
    }

    // OBTER ESTADO FORMATADO
    public function getEstadoFormatado(): string
    {
        $labels = self::optsEstado();
        $label = $labels[$this->estado] ?? '-';

        $colors = [
            self::ESTADO_ATIVO => '',
            self::ESTADO_ENCERRADO => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    // VERIFICAR SE TEM SESSÕES ATIVAS
    public function hasSessoesAtivas(): bool
    {
        foreach ($this->sessaos as $sessao) {
            // IGNORAR SESSÕES TERMINADAS
            if ($sessao->isEstadoTerminada()) {
                continue;
            }

            // SE TEM SESSÕES ATIVAS --> ESTÁ ATIVA
            if (!$sessao->isDeletable()) {
                return true;
            }
        }

        // NENHUMA SESSÃO ATIVA
        return false;
    }

    // VERIFICAR SE TEM ALUGUERES ATIVOS
    public function hasAlugueresAtivos(): bool
    {
        return $this->getAluguerSalas()
            ->where(['estado' => [
                AluguerSala::ESTADO_A_DECORRER,
                AluguerSala::ESTADO_CONFIRMADO,
                AluguerSala::ESTADO_PENDENTE]])
            ->exists();
    }

    // VERIFICAR SE NOVO HORÁRIO TEM CONFLITOS
    public function hasConflitosHorario(): bool
    {
        $agora = date('Y-m-d H:i:s');

        $sessoes = Sessao::find()
            ->where(['cinema_id' => $this->id])
            ->andWhere(['>', 'data_hora_fim', $agora])
            ->andWhere([
                'or',
                ['<', 'hora_inicio', $this->hora_abertura],
                ['>', 'hora_fim', $this->hora_fecho],
            ])
            ->exists();

        $alugueres = AluguerSala::find()
            ->where(['cinema_id' => $this->id])
            ->andWhere(['estado' => AluguerSala::ESTADO_CONFIRMADO])
            ->andWhere(['>', 'data_fim', $agora]) // futuros
            ->andWhere([
                'or',
                ['<', 'hora_inicio', $this->hora_abertura],
                ['>', 'hora_fim', $this->hora_fecho],
            ])
            ->exists();

        return $sessoes || $alugueres;
    }

    // OBTER MORADA COMPLETA
    public function getMorada(): string
    {
        return $this->rua . ', ' . $this->codigo_postal . ' ' . $this->cidade;
    }

    // OBTER NÚMERO DE LUGARES
    public function getNumeroLugares(): int
    {
        $lugares = 0;
        foreach ($this->salas as $sala) {
            $lugares += ($sala->num_colunas * $sala->num_filas);
        }

        return $lugares;
    }

    // TOTAL DE SALAS
    public function getTotalSalas()
    {
        return count($this->salas);
    }

    // OBTER O NOME DO GERENTE
    public function getNomeGerente()
    {
        return $this->gerente->profile->nome ?? null;
    }


    // HORA INÍCIO FORMATADA (HH:mm)
    public function getHoraInicioFormatada()
    {
        return Yii::$app->formatter->asTime($this->hora_inicio, 'php:H:i');
    }

    // HORA FIM FORMATADA (HH:mm)
    public function getHoraFimFormatada()
    {
        return Yii::$app->formatter->asTime($this->hora_fim, 'php:H:i');
    }

    // OBTER HORA JUNTA
    public function getHorario()
    {
        return Yii::$app->formatter->asTime($this->horario_abertura, 'php:H:i')
            . ' - ' .
            Yii::$app->formatter->asTime($this->horario_fecho, 'php:H:i');
    }

    // OBTER SESSÕES FUTURAS DESTE CINEMA (FILME OPCIONAL)
    public function getSessoesFuturas($filmeId = null)
    {
        $query = $this->getSessaos()
            ->andWhere(['>=', 'data', date('Y-m-d')])
            ->orderBy(['data' => SORT_ASC, 'hora_inicio' => SORT_ASC]);

        if ($filmeId !== null) {
            $query->andWhere(['filme_id' => $filmeId]);
        }

        return $query->all();
    }

    // VERIFICAR SE PODE SER EDITADO
    public function isEditable(): bool { return true; }

    // VERIFICAR SE PODE SER ATIVADO
    public function isActivatable(): bool
    {
        return $this->estado === self::ESTADO_ENCERRADO;
    }

    // VERIFICAR SE PODE SER ENCERRADO
    public function isClosable(): bool
    {
        return $this->estado === self::ESTADO_ATIVO && !$this->hasSessoesAtivas() && !$this->hasAlugueresAtivos();
    }

    /**
     * Gets query for [[Gerente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGerente()
    {
        return $this->hasOne(User::class, ['id' => 'gerente_id']);
    }

    /**
     * Gets query for [[Salas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['cinema_id' => 'id']);
    }

    /**
     * Gets query for [[Sessaos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessaos()
    {
        return $this->hasMany(Sessao::class, ['cinema_id' => 'id']);
    }

    /**
     * Gets query for [[UserProfiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfiles()
    {
        return $this->hasMany(UserProfile::class, ['cinema_id' => 'id']);
    }

    /**
     * Gets query for [[AluguerSalas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAluguerSalas()
    {
        return $this->hasMany(AluguerSala::class, ['cinema_id' => 'id']);
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVO => 'Ativo',
            self::ESTADO_ENCERRADO => 'Encerrado',
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
    public function isEstadoAtivo()
    {
        return $this->estado === self::ESTADO_ATIVO;
    }

    public function setEstadoToAtivo()
    {
        $this->estado = self::ESTADO_ATIVO;
    }

    /**
     * @return bool
     */
    public function isEstadoEncerrado()
    {
        return $this->estado === self::ESTADO_ENCERRADO;
    }

    public function setEstadoToEncerrado()
    {
        $this->estado = self::ESTADO_ENCERRADO;
    }
}
