<?php

namespace common\models;

use common\helpers\Formatter;
use common\helpers\MqttService;
use DateTime;
use Yii;

/**
 * This is the model class for table "sessao".
 *
 * @property int $id
 * @property string $data
 * @property string $hora_inicio
 * @property string $hora_fim
 * @property int $filme_id
 * @property int $sala_id
 * @property int $cinema_id
 *
 * @property string $estado
 *
 * @property-read $nome
 * @property-read $horario
 * @property-read array $lugaresOcupados
 * @property-read array $lugaresConfirmados
 * @property-read string $numeroLugaresDisponiveis
 *
 * @property Compra[] $compras
 * @property Bilhete[] $bilhetes
 * @property Cinema $cinema
 * @property Filme $filme
 * @property Sala $sala
 */
class Sessao extends \yii\db\ActiveRecord
{
    const ESTADO_ATIVA = 'Ativa';
    const ESTADO_A_DECORRER = 'A decorrer';
    const ESTADO_ESGOTADA = 'Esgotada';
    const ESTADO_TERMINADA = 'Terminada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sessao';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data', 'hora_inicio', 'hora_fim', 'filme_id', 'sala_id', 'cinema_id'], 'required'],
            [['data', 'hora_inicio', 'hora_fim'], 'safe'],
            [['filme_id', 'sala_id', 'cinema_id'], 'integer'],
            [['cinema_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cinema::class, 'targetAttribute' => ['cinema_id' => 'id']],
            [['filme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Filme::class, 'targetAttribute' => ['filme_id' => 'id']],
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
            'data' => 'Data',
            'hora_inicio' => 'Hora Início',
            'hora_fim' => 'Hora Fim',
            'horario' => 'Horário',
            'filme_id' => 'Filme',
            'tituloFilme' => 'Filme',
            'sala_id' => 'Sala',
            'cinema_id' => 'Cinema',
            'numeroLugaresDisponiveis' => 'Lugares Disponíveis',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Só publica mensagem se estiver esgotada
        if (array_key_exists('estado', $changedAttributes) &&
            $this->estado === self::ESTADO_ESGOTADA) {

            // Mensagem que o cliente recebe
            $toast = "A sessão #{$this->id} esta esgotada.";
            // Topic de alugueres de este cliente
            $topic = "cinelive/sessoes/esgotadas";

            // Criar a mensagem a enviar
            $message = json_encode([
                'id' => $this->id,
                'estado' => $this->estado,
                'mensagem' => $toast,
                'titulo' => $this->filme->titulo,
                'sala' => $this->sala->nome,
                'cinema' => $this->cinema->nome,
                'data' => $this->data,
                'hora_inicio' => $this->hora_inicio,
            ]);

            // Publicar via serviço MQTT
            MqttService::publish($topic, $message);
        }
    }

    public function getNome(): string
    {
        return "Sessão #{$this->id}";
    }

    public function getHorario()
    {
        return Formatter::horario($this->hora_inicio, $this->hora_fim);
    }

    public function getEstado()
    {
        $now = new DateTime();

        $inicio = new DateTime("{$this->data} {$this->hora_inicio}");
        $fim = new DateTime("{$this->data} {$this->hora_fim}");

        if ($now > $fim) return self::ESTADO_TERMINADA;

        if ($now > $inicio && $fim > $now) return self::ESTADO_A_DECORRER;

        if (count($this->lugaresOcupados) >= $this->sala->numeroLugares) return self::ESTADO_ESGOTADA;

        return self::ESTADO_ATIVA;
    }

    public function getEstadoHtml(): string
    {
        $label = $this->displayEstado() ?? '-';

        $colors = [
            self::ESTADO_ATIVA => '',
            self::ESTADO_A_DECORRER => 'text-danger',
            self::ESTADO_ESGOTADA => 'text-secondary',
            self::ESTADO_TERMINADA => 'text-secondary font-italic',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    public function getCompraIdPorLugar(string $lugar): ?int
    {
        return $this->getBilhetes()
            ->select('compra_id')
            ->andWhere(['lugar' => $lugar])
            ->andWhere(['!=', 'estado', Bilhete::ESTADO_CANCELADO])
            ->scalar() ?: null;
    }

    public function getLugaresOcupados(): array
    {
        return $this->getBilhetes()
            ->select('lugar')
            ->andWhere(['!=', 'estado', Bilhete::ESTADO_CANCELADO])
            ->column();
    }

    public function getLugaresConfirmados(): array
    {
        return $this->getBilhetes()
            ->select('lugar')
            ->andWhere(['estado' => Bilhete::ESTADO_CONFIRMADO])
            ->column();
    }

    public function getNumeroLugaresDisponiveis(): int
    {
        return $this->sala->numeroLugares - count($this->lugaresOcupados);
    }

    public function getHoraFimCalculada($duracaoFilme): ?string
    {
        if (!$this->hora_inicio || !$duracaoFilme) {
            return null;
        }

        $inicio = new DateTime($this->hora_inicio);
        $inicio->modify("+{$duracaoFilme} minutes");

        return $inicio->format('H:i');
    }

    public function validateHorario(): bool
    {
        $now = new DateTime();
        $inicio = new DateTime("{$this->data} {$this->hora_inicio}");
        $fim = new DateTime("{$this->data} {$this->hora_fim}");

        if ($fim <= $inicio) {
            Yii::$app->session->setFlash('error', 'A hora de fim deve ser posterior à hora de início.');
            return false;
        }

        if ($inicio < $now) {
            Yii::$app->session->setFlash('error', 'A sessão não pode começar no passado.');
            return false;
        }

        $cinema = $this->cinema ?? null;

        if ($cinema) {
            $abertura = new DateTime("{$this->data} {$cinema->horario_abertura}");
            $fecho = new DateTime("{$this->data} {$cinema->horario_fecho}");

            if ($inicio < $abertura) {
                Yii::$app->session->setFlash('error', "O cinema ainda não está aberto à hora selecionada.");
                return false;
            }

            if ($fim > $fecho) {
                Yii::$app->session->setFlash('error', "A sessão não pode ultrapassar a horário do fecho do cinema.");
                return false;
            }

            $salasDisponiveis = $cinema->getSalasDisponiveis(
                $this->data, $this->hora_inicio, $this->hora_fim, $this->id ? $this->sala_id : null
            );

            if (!in_array($this->sala, $salasDisponiveis)) {
                Yii::$app->session->setFlash('error', "A sala selecionada não está disponível no horário selecionado.");
                return false;
            }
        }

        return true;
    }

    public function isEditable(): bool
    {
        return $this->estado !== self::ESTADO_A_DECORRER
            && $this->estado !== self::ESTADO_TERMINADA;
    }

    public function isDeletable(): bool
    {
        return count($this->lugaresOcupados) === 0
            && $this->estado !== self::ESTADO_A_DECORRER;
    }

    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVA => 'Ativa',
            self::ESTADO_A_DECORRER => 'A decorrer',
            self::ESTADO_ESGOTADA => 'Esgotada',
            self::ESTADO_TERMINADA => 'Terminada',
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
    public function isEstadoADecorrer()
    {
        return $this->estado === self::ESTADO_A_DECORRER;
    }

    public function setEstadoToADecorrer()
    {
        $this->estado = self::ESTADO_A_DECORRER;
    }

    /**
     * @return bool
     */
    public function isEstadoEsgotada()
    {
        return $this->estado === self::ESTADO_ESGOTADA;
    }

    public function setEstadoToEsgotada()
    {
        $this->estado = self::ESTADO_ESGOTADA;
    }

    /**
     * @return bool
     */
    public function isEstadoTerminada()
    {
        return $this->estado === self::ESTADO_TERMINADA;
    }

    public function setEstadoToTerminada()
    {
        $this->estado = self::ESTADO_TERMINADA;
    }

    /**
     * Gets query for [[Compras]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompras()
    {
        return $this->hasMany(Compra::class, ['sessao_id' => 'id']);
    }

    /**
     * Gets query for [[Bilhetes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBilhetes()
    {
        return $this->hasMany(Bilhete::class, ['compra_id' => 'id'])
            ->via('compras');
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
     * Gets query for [[Filme]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilme()
    {
        return $this->hasOne(Filme::class, ['id' => 'filme_id']);
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

}
