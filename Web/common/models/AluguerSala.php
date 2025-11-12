<?php

namespace common\models;

use common\components\EmailHelper;
use DateTime;
use Yii;

/**
 * This is the model class for table "aluguer_sala".
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $cinema_id
 * @property int $sala_id
 * @property string $data
 * @property string $hora_inicio
 * @property string $hora_fim
 * @property string $estado
 * @property string $tipo_evento
 * @property string $observacoes
 *
 * @property Cinema $cinema
 * @property User $cliente
 * @property Sala $sala
 */
class AluguerSala extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ESTADO_PENDENTE = 'pendente';
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_CANCELADO = 'cancelado';
    const ESTADO_A_DECORRER = 'a_decorrer';
    const ESTADO_TERMINADO = 'terminado';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'aluguer_sala';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cliente_id', 'cinema_id', 'sala_id', 'data', 'hora_inicio', 'hora_fim', 'estado', 'tipo_evento', 'observacoes'], 'required'],
            [['cliente_id', 'cinema_id', 'sala_id'], 'integer'],
            [['data', 'hora_inicio', 'hora_fim'], 'safe'],
            [['estado', 'observacoes'], 'string'],
            [['tipo_evento'], 'string', 'max' => 100],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['cinema_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cinema::class, 'targetAttribute' => ['cinema_id' => 'id']],
            [['cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['cliente_id' => 'id']],
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
            'cliente_id' => 'Cliente',
            'cinema_id' => 'Cinema',
            'sala_id' => 'Sala',
            'data' => 'Data',
            'hora_inicio' => 'Hora Início',
            'hora_fim' => 'Hora Fim',
            'estado' => 'Estado',
            'tipo_evento' => 'Tipo de Evento',
            'observacoes' => 'Observações',
            'horaInicioFormatada' => 'Hora Início',
            'horaFimFormatada' => 'Hora Fim',
        ];
    }

    // OBTER ESTADOS PERSONALIZADOS
    public function atualizarEstadoAutomatico(): void
    {
        // AGORA
        $now = new DateTime();

        // DATAS DE INÍCIO E FIM
        $inicio = new DateTime("{$this->data} {$this->hora_inicio}");
        $fim = new DateTime("{$this->data} {$this->hora_fim}");

        // SE ESTIVER PENDENTE E A HORA DE INÍCIO JÁ PASSOU --> CANCELAR AUTOMATICAMENTE
        if ($this->estado === self::ESTADO_PENDENTE && $now > $inicio) {
            $this->estado = self::ESTADO_CANCELADO;
            $this->save(false, ['estado']);
        }

        // SÓ ATUALIZAR SE ESTADO ANTERIOR FOSSE CONFIRMADO
        if ($this->estado === self::ESTADO_CONFIRMADO) {
            if ($now >= $inicio && $now <= $fim) {
                $this->estado = self::ESTADO_A_DECORRER;
            } elseif ($now > $fim) {
                $this->estado = self::ESTADO_TERMINADO;
            }
        }
    }

    // MOSTRAR ESTADOS (A DECORRER/TERMINADO)
    public function afterFind()
    {
        parent::afterFind();
        $this->atualizarEstadoAutomatico();
    }

    public function getEstadoOptions(): array
    {
        $estados = self::optsEstadoBD();

        if ($this->estado === self::ESTADO_CONFIRMADO) {
            unset($estados[self::ESTADO_PENDENTE]);
        }

        if (!isset($estados[$this->estado])) {
            $estados[$this->estado] = $this->displayEstado();
        }

        return $estados;
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

    // OBTER ESTADO FORMATADO
    public function getEstadoFormatado()
    {
        $label = self::optsEstado()[$this->estado] ?? '-';
        $cores = [
            self::ESTADO_PENDENTE => 'text-secondary',
            self::ESTADO_CONFIRMADO => '',
            self::ESTADO_A_DECORRER => 'text-danger',
            self::ESTADO_TERMINADO => 'text-secondary font-italic',
            self::ESTADO_CANCELADO => 'text-secondary font-italic',
        ];
        $classe = $cores[$this->estado] ?? 'text-secondary';
        return "<span class='{$classe}'>{$label}</span>";
    }

    // VALIDAR O HORÁRIO DO ALUGUER
    public function validateHorario(): bool
    {
        // OBTER DATAS DE HOJE E INÍCIO E FIM DO ALUGUER
        $now = new DateTime();
        $dataHoraInicio = new DateTime("{$this->data} {$this->hora_inicio}");
        $dataHoraFim = new DateTime("{$this->data} {$this->hora_fim}");

        // VERIFICAÇÕES BÁSICAS
        if ($dataHoraFim <= $dataHoraInicio) {
            Yii::$app->session->setFlash('error', 'A hora de fim deve ser posterior à hora de início.');
            return false;
        }

        if ($dataHoraInicio < $now) {
            Yii::$app->session->setFlash('error', 'A hora de início não pode ser anterior à hora atual.');
            return false;
        }

        // OBTER CINEMA RELACIONADO
        $cinema = $this->cinema ?? null;
        if ($cinema) {
            $abertura = new DateTime("{$this->data} {$cinema->horario_abertura}");
            $fecho = new DateTime("{$this->data} {$cinema->horario_fecho}");

            if ($dataHoraInicio < $abertura) {
                Yii::$app->session->setFlash('error', "O cinema ainda não está aberto às {$cinema->horarioAberturaFormatado}.");
                return false;
            }

            if ($dataHoraFim > $fecho) {
                Yii::$app->session->setFlash('error', "O cinema encerra às {$cinema->horarioFechoFormatado}. O aluguer não pode ultrapassar esse horário.");
                return false;
            }
        }

        // VERIFICAR CONFLITOS COM SESSÕES
        $sessaoExiste = Sessao::find()
            ->where(['sala_id' => $this->sala_id])
            ->andWhere(['data' => $this->data])
            ->andWhere(['and',
                ['<', 'hora_inicio', $this->hora_fim],
                ['>', 'hora_fim', $this->hora_inicio]
            ])
            ->exists();

        if ($sessaoExiste) {
            Yii::$app->session->setFlash('error', 'Já existe uma sessão agendada nesta sala que se sobrepõe ao horário pretendido.');
            return false;
        }

        // VERIFICAR CONFLITOS COM OUTROS ALUGUERES
        $aluguerExiste = self::find()
            ->where(['sala_id' => $this->sala_id])
            ->andWhere(['data' => $this->data])
            ->andWhere(['estado' => [
                self::ESTADO_CONFIRMADO,
                self::ESTADO_A_DECORRER,
            ]])
            ->andWhere(['and',
                ['<', 'hora_inicio', $this->hora_fim],
                ['>', 'hora_fim', $this->hora_inicio]
            ])
            ->andWhere(['!=', 'id', $this->id ?? 0])
            ->exists();

        if ($aluguerExiste) {
            Yii::$app->session->setFlash('error', 'Já existe um aluguer ativo nesta sala que se sobrepõe ao horário pretendido.');
            return false;
        }

        return true;
    }

    // ENVIAR EMAIL AO CLIENTE CONFORME O NOVO ESTADO DO PEDIDO DE ALUGUER
    public function enviarEmailEstado(?string $novoEstado = null): bool
    {
        $novoEstado = $novoEstado ?? $this->estado;

        // OBTER CLIENTE
        $cliente = $this->cliente;
        if (!$cliente) return false;

        $nome = $cliente->profile->nome ?? $cliente->username;
        $email = $cliente->email;

        switch ($novoEstado) {
            case self::ESTADO_CONFIRMADO:
                $assunto = 'Confirmação do aluguer de sala - CineLive';
                $mensagem = "
                    <p>Olá <strong>{$nome}</strong>,</p>
                    <p>O seu <b>aluguer de sala #{$this->id}</b> foi <span style='color:green;'>confirmado</span> com sucesso!</p>
                    <p><b>Data:</b> {$this->dataFormatada}<br>
                       <b>Hora:</b> {$this->horaInicioFormatada} - {$this->horaFimFormatada}<br>
                       <b>Sala:</b> {$this->sala->nome}</p>
                    <p style='margin-top:0.75rem;'>Obrigado por escolher o CineLive.<br><b>Até breve!</b></p>";
                break;

            case self::ESTADO_CANCELADO:
                $assunto = 'Cancelamento do aluguer de sala - CineLive';
                $mensagem = "
                    <p>Olá <strong>{$nome}</strong>,</p>
                    <p>O seu <b>aluguer de sala #{$this->id}</b> foi <span style='color:#c00;'>cancelado</span>.</p>
                    <p>Se desejar reagendar, entre em contacto com o cinema.</p>
                    <p style='margin-top:0.75rem;'>Cumprimentos,<br><b>Equipa CineLive</b></p>";
                break;

            default:
                return false;
        }

        // ENVIAR EMAIL
        EmailHelper::enviarEmail($email, $assunto, $mensagem);
        return true;
    }

    public function isDeletable(): bool
    {
        return $this->estado === self::ESTADO_CANCELADO || $this->estado === self::ESTADO_PENDENTE;
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
     * Gets query for [[Cliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCliente()
    {
        return $this->hasOne(User::class, ['id' => 'cliente_id']);
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


    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_PENDENTE => 'Pendente',
            self::ESTADO_A_DECORRER => 'A decorrer',
            self::ESTADO_CONFIRMADO => 'Confirmado',
            self::ESTADO_CANCELADO => 'Cancelado',
            self::ESTADO_TERMINADO => 'Terminado',
        ];
    }

    public static function optsEstadoBD()
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
    public function isEstadoTerminado()
    {
        return $this->estado === self::ESTADO_TERMINADO;
    }

    public function setEstadoToTerminado()
    {
        $this->estado = self::ESTADO_TERMINADO;
    }
}
