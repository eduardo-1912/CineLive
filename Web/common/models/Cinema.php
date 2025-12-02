<?php

namespace common\models;

use common\helpers\Formatter;
use yii\helpers\ArrayHelper;

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
 * @property-read string morada
 * @property-read string $horario
 * @property-read string $estadoHtml
 *
 * @property AluguerSala[] $aluguerSalas
 * @property User $gerente
 * @property Sala[] $salas
 * @property Sessao[] $sessoes
 * @property Compra[] $compras
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
            'morada' => 'Morada',
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
            'capacidade' => 'Capacidade',
            'gerente_id', 'nomeGerente' => 'Gerente',
        ];
    }

    public function getMorada(): string
    {
        return "{$this->rua}, {$this->codigo_postal} {$this->cidade}";
    }

    public function getHorario(): string
    {
        return Formatter::horario($this->horario_abertura, $this->horario_fecho);
    }

    public function getNumeroSalas(): int
    {
        return count($this->salas);
    }

    public function getNumeroLugares(): int
    {
        $totalLugares = 0;
        foreach ($this->salas as $sala) {
            $totalLugares += $sala->getNumeroLugares();
        }

        return $totalLugares;
    }

    public function getEstadoHtml(): string
    {
        $label = $this->displayEstado() ?? '-';

        $colors = [
            self::ESTADO_ATIVO => '',
            self::ESTADO_ENCERRADO => 'text-danger',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    public static function findAllList(): array
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'nome');
    }

    public static function findAtivos(): array
    {
        return self::find()->where(['estado' => Cinema::ESTADO_ATIVO])->all();
    }

    public static function findAtivosList(): array
    {
        return ArrayHelper::map(self::findAtivos(), 'id', 'nome');
    }

    public static function findComSessoesAtivas(): array
    {
        return array_filter(self::findAtivos(), fn($cinema) => !empty($cinema->getSessoesAtivas()));
    }

    public function getSessoesAtivas(): array
    {
        return array_filter($this->sessoes, fn($sessao) => $sessao->isEstadoAtiva());
    }

    public function getProximoNumeroSala(): int
    {
        return ($this->getSalas()->max('numero') ?? 0) + 1;
    }

    public function getSalasDisponiveis($data, $horaInicio, $horaFim, $salaAtualId = null)
    {
        $salasOcupadas = $this->getSessoes()
            ->select('sala_id')
            ->andWhere(['data' => $data])
            ->andWhere(['<', 'hora_inicio', $horaFim])
            ->andWhere(['>', 'hora_fim', $horaInicio])
            ->column();

        $salasAlugadas = $this->getAluguerSalas()
            ->select('sala_id')
            ->where([
                'cinema_id' => $this->id,
                'data' => $data,
            ])
            ->andWhere(['estado' => [
                AluguerSala::ESTADO_PENDENTE,
                AluguerSala::ESTADO_CONFIRMADO,
                AluguerSala::ESTADO_A_DECORRER,
            ]])
            ->andWhere(['<', 'hora_inicio', $horaFim])
            ->andWhere(['>', 'hora_fim', $horaInicio])
            ->column();

        $salasIndisponiveis = array_unique(array_merge($salasOcupadas, $salasAlugadas));

        if ($salaAtualId) {
            $salasIndisponiveis = array_diff($salasIndisponiveis, [$salaAtualId]);
        }

        return $this->getSalas()
            ->where(['estado' => Sala::ESTADO_ATIVA])
            ->andFilterWhere(['not in', 'id', $salasIndisponiveis])
            ->orderBy(['numero' => SORT_ASC])
            ->all();
    }

    public function getFilmesComSessoesAtivas($kids = false, $q = null): array
    {
        $filmes = [];

        // Obter filmes
        foreach ($this->getSessoesAtivas() as $sessao)
            $filmes[$sessao->filme->id] = $sessao->filme;

        // Filtro de kids
        if ($kids === true) {
            $filmes = array_filter($filmes, fn($filme) => in_array($filme->rating, Filme::optsRatingKids()));
        }

        // Query
        elseif ($q) {
            $filmes = array_filter($filmes, fn($f) => stripos($f->titulo, $q) !== false);
        }

        // Ordernar
        usort($filmes, function ($a, $b) {
            return strcmp($a->titulo, $b->titulo);
        });

        return $filmes;
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

    public function validateHorario(): bool
    {
        foreach ($this->getSessoesValidas() as $sessao) {
            if ($sessao->hora_inicio < $this->horario_abertura ||
                $sessao->hora_fim > $this->horario_fecho) {
                return false;
            }
        }

        foreach ($this->getAlugueresValidos() as $aluguer) {
            if ($aluguer->hora_inicio < $this->horario_abertura ||
                $aluguer->hora_fim > $this->horario_fecho) {
                return false;
            }
        }

        return true;
    }

    public function isEditable(): bool {
        return true;
    }

    public function isActivatable(): bool
    {
        return $this->estado === self::ESTADO_ENCERRADO;
    }

    public function isClosable(): bool
    {
        return $this->estado === self::ESTADO_ATIVO
            && empty($this->getSessoesValidas())
            && empty($this->getAlugueresValidos());
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
     * Gets query for [[Sessões]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessoes()
    {
        return $this->hasMany(Sessao::class, ['cinema_id' => 'id']);
    }

    /**
     * Gets query for [[Compras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompras()
    {
        return $this->hasMany(Compra::class, ['sessao_id' => 'id'])->via('sessoes');
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
