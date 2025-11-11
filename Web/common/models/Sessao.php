<?php

namespace common\models;

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
            'filme_id' => 'Filme',
            'sala_id' => 'Sala',
            'cinema_id' => 'Cinema',
        ];
    }

    // OBTER O NOME
    public function getNome()
    {
        return 'Sessão #' . $this->id;
    }
    
    // OBTER O ESTADO DA SESSÃO
    public function getEstado()
    {
        // OBTER DATA E HORA ATUAL
        $now = new DateTime();

        // OBTER DATA E HORA INÍCIO E FIM DA SESSÃO
        $dataHoraInicio = new DateTime("{$this->data} {$this->hora_inicio}");
        $dataHoraFim = new DateTime("{$this->data} {$this->hora_fim}");

        if (!$this->sala) {
            return self::ESTADO_ATIVA;
        }

        // NÚMERO DE LUGARES OCUPADOS
        $lugaresOcupados = count($this->lugaresOcupados ?? 0);

        // ESTADOS
        if ($now > $dataHoraFim) { return self::ESTADO_TERMINADA; }
        if ($now > $dataHoraInicio && $dataHoraFim > $now) { return self::ESTADO_A_DECORRER; }
        if ($lugaresOcupados >= $this->sala->lugares) { return self::ESTADO_ESGOTADA; }
        return self::ESTADO_ATIVA;
    }

    // OBTER ESTADO FORMATADO (PARA /INDEX E /VIEW)
    public function getEstadoFormatado(): string
    {
        $labels = self::optsEstado();
        $label = $labels[$this->estado] ?? '-';

        $colors = [
            self::ESTADO_ATIVA => '',
            self::ESTADO_A_DECORRER => 'text-danger',
            self::ESTADO_ESGOTADA => 'text-secondary',
            self::ESTADO_TERMINADA => 'text-secondary font-italic',
        ];

        $class = $colors[$this->estado] ?? 'text-secondary';
        return "<span class='{$class}'>{$label}</span>";
    }

    // OBTER ARRAY DE LUGARES OCUPADOS
    public function getLugaresOcupados()
    {
        return $this->getBilhetes()
            ->select('lugar')
            ->andWhere(['<>', 'estado', Bilhete::ESTADO_CANCELADO])
            ->column();
    }

    // OBTER ARRAY DE LUGARES CONFIRMADOS
    public function getLugaresConfirmados()
    {
        return $this->getBilhetes()->select('lugar')
            ->andWhere(['estado' => Bilhete::ESTADO_CONFIRMADO])->column();
    }

    // OBTER COMPRA_ID PARA CADA LUGAR DA SESSÃO
    public function getMapaLugaresCompra()
    {
        return $this->getBilhetes()->select(['compra_id', 'lugar'])->indexBy('lugar')->column();
    }

    public function getNumeroLugaresDisponiveis()
    {
        return $this->sala->lugares - count($this->lugaresOcupados);
    }

    // OBTER DATA FORMATADA (DD/MM/AAAA)
    public function getDataFormatada()
    {
        return Yii::$app->formatter->asDate($this->data, 'php:d/m/Y');
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
    public function getHora()
    {
        return Yii::$app->formatter->asTime($this->hora_inicio, 'php:H:i')
        . ' - ' .
        Yii::$app->formatter->asTime($this->hora_fim, 'php:H:i');
    }

    // CALCULAR A HORA FIM CONSOANTE FILME SELECIONADO E HORA INÍCIO
    public function getHoraFimCalculada($duracaoMinutos)
    {
        if (!$this->hora_inicio || !$duracaoMinutos) {
            return null;
        }

        $inicio = new DateTime($this->hora_inicio);
        $inicio->modify("+{$duracaoMinutos} minutes");

        return $inicio->format('H:i');
    }


    // VERIFICAR SE A SESSÃO PODE SER EDITADA
    public function isEditable(): bool
    {
        return $this->estado !== self::ESTADO_A_DECORRER && $this->estado !== self::ESTADO_TERMINADA;
    }

    // VERIFICAR SE A SESSÃO PODE SER ELIMINADA
    public function isDeletable(): bool
    {
        return count($this->lugaresOcupados) === 0 && $this->estado !== self::ESTADO_A_DECORRER;
    }

    // VALIDAR O HORÁRIO DA SESSÃO
    public function validateHorario(): bool
    {
        // OBTER DATAS DE HOJE E INÍCIO E FIM DE SESSÃO
        $now = new DateTime();
        $dataHoraInicio = new DateTime("{$this->data} {$this->hora_inicio}");
        $dataHoraFim = new DateTime("{$this->data} {$this->hora_fim}");

        // SE A HORA DE FIM FOR ANTERIOR À HORA DE INÍCIO --> MENSAGEM DE ERRO
        if ($dataHoraFim <= $dataHoraInicio) {
            Yii::$app->session->setFlash('error', 'A hora de fim deve ser posterior à hora de início.');
            return false;
        }

        // SE A SESSÃO FOR HOJE E A HORA DE INÍCIO JÁ TIVER PASSADO --> MENSAGEM DE ERRO
        if ($dataHoraInicio < $now) {
            Yii::$app->session->setFlash('error', 'A hora de início não pode ser anterior à hora atual.');
            return false;
        }

        // OBTER CINEMA RELACIONADO
        $cinema = $this->cinema ?? null;

        if ($cinema) {
            // OBTER HORARIO ABERTURA E FECHO
            $abertura = new DateTime("{$this->data} {$cinema->horario_abertura}");
            $fecho = new DateTime("{$this->data} {$cinema->horario_fecho}");

            if ($dataHoraInicio < $abertura) {
                Yii::$app->session->setFlash('error', "O cinema ainda não está aberto às {$cinema->horarioAberturaFormatado}.");
                return false;
            }

            if ($dataHoraFim > $fecho) {
                Yii::$app->session->setFlash('error', "O cinema encerra às {$cinema->horarioFechoFormatado}. A sessão não pode ultrapassar esse horário.");
                return false;
            }
        }

        // VERIFICAR CONFLITOS COM OUTRAS SESSÕES
        $sessoes = self::find()
            ->where(['sala_id' => $this->sala_id])
            ->andWhere(['data' => $this->data])
            ->andWhere(['and',
                ['<', 'hora_inicio', $this->hora_fim],
                ['>', 'hora_fim', $this->hora_inicio]
            ])
            ->andWhere(['!=', 'id', $this->id ?? 0])
            ->exists();

        if ($sessoes) {
            Yii::$app->session->setFlash('error', 'Já existe uma sessão nesta sala que se sobrepõe a este horário.');
            return false;
        }

        // VERIFICAR CONFLITOS COM ALUGUERES DE SALA
        $aluguerExiste = AluguerSala::find()
            ->where(['sala_id' => $this->sala_id])
            ->andWhere(['data' => $this->data])
            ->andWhere(['estado' => [
                AluguerSala::ESTADO_CONFIRMADO,
                AluguerSala::ESTADO_A_DECORRER,
            ]])
            ->andWhere(['and',
                ['<', 'hora_inicio', $this->hora_fim],
                ['>', 'hora_fim', $this->hora_inicio]
            ])
            ->exists();

        if ($aluguerExiste) {
            Yii::$app->session->setFlash('error', 'Esta sala encontra-se alugada neste horário.');
            return false;
        }

        return true;
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
        return $this->hasOne(Compra::class, ['sessao_id' => 'id']);
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
