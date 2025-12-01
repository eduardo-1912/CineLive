<?php

namespace common\models;

use common\helpers\Formatter;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "filme".
 *
 * @property int $id
 * @property string $titulo
 * @property string $sinopse
 * @property int $duracao
 * @property string $rating
 * @property string $estreia
 * @property string $idioma
 * @property string $realizacao
 * @property string $trailer_url
 * @property string $poster_path
 * @property string $estado
 *
 * @property-read $posterUrl
 *
 * @property Genero[] $generos
 * @property Sessao[] $sessoes
 */
class Filme extends \yii\db\ActiveRecord
{
    /** @var UploadedFile|null */
    public $posterFile;

    /** @var array|null IDs dos géneros selecionados (campo virtual) */
    public $generosSelecionados;

    /**
     * ENUM field values
     */
    const RATING_TODOS = 'Todos';
    const RATING_M3 = 'M3';
    const RATING_M6 = 'M6';
    const RATING_M12 = 'M12';
    const RATING_M14 = 'M14';
    const RATING_M16 = 'M16';
    const RATING_M18 = 'M18';
    const ESTADO_BREVEMENTE = 'brevemente';
    const ESTADO_EM_EXIBICAO = 'em_exibicao';
    const ESTADO_TERMINADO = 'terminado';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'filme';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'sinopse', 'duracao', 'rating', 'estreia', 'idioma', 'realizacao', 'trailer_url', 'estado'], 'required'],
            [['sinopse', 'estado'], 'string'],
            [['duracao'], 'integer'],
            [['estreia', 'generosSelecionados'], 'safe'],
            [['titulo', 'trailer_url', 'poster_path'], 'string', 'max' => 255],
            [['idioma'], 'string', 'max' => 50],
            [['realizacao'], 'string', 'max' => 80],
            ['rating', 'in', 'range' => array_keys(self::optsRating())],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            ['posterFile', 'file', 'skipOnEmpty' => true,
                'extensions' => ['png','jpg','jpeg','webp'],
                'maxSize' => 2 * 1024 * 1024, // 2MB
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Título',
            'sinopse' => 'Sinopse',
            'duracao' => 'Duração',
            'duracaoHoras' => 'Duração',
            'rating' => 'Rating',
            'estreia' => 'Estreia',
            'idioma' => 'Idioma',
            'realizacao' => 'Realização',
            'trailer_url' => 'Trailer',
            'poster_path', 'posterFile', 'posterUrl' => 'Poster',
            'estado' => 'Estado',
        ];
    }

    public static function findComSessoesAtivas($limit = null)
    {
        $filmes = array_filter(
            self::find()->orderBy(['id' => SORT_DESC])->all(),
            fn($filme) => !empty($filme->getSessoesAtivas())
        );

        if ($limit) {
            $filmes = array_slice($filmes, 0, $limit);
        }

        return $filmes;
    }

    public function getSessoesAtivas($cinemaId = null): array
    {
        $sessoes = array_filter($this->sessoes, fn($sessao) => $sessao->isEstadoAtiva());

        if ($cinemaId) {
            return array_filter($sessoes, fn($sessao) => $sessao->cinema_id == $cinemaId);
        }

        return $sessoes;
    }

    public function getSessoesAtivasPorData($cinemaId = null): array
    {
        $sessoes = $this->getSessoesAtivas($cinemaId);
        usort($sessoes, fn($a, $b) => strcmp($a->hora_inicio, $b->hora_inicio));

        $sessoesPorData = [];
        foreach ($sessoes as $sessao) {
            $sessoesPorData[Formatter::data($sessao->data)][] = $sessao;
        }

        ksort($sessoesPorData);
        return $sessoesPorData;
    }

    public function getCinemasComSessoesAtivas(): array
    {
        $cinemas = [];
        foreach ($this->getSessoesAtivas() as $sessao) {
            $cinema = $sessao->cinema;
            if ($cinema->isEstadoAtivo()) {
                $cinemas[$cinema->id] = $cinema;
            }
        }

        return $cinemas;
    }

    public function getPosterUrl(): string
    {
        $local = Yii::getAlias(Yii::$app->params['posterPath']);
        $url = Yii::$app->params['posterUrl'];

        $file = "{$local}/{$this->poster_path}";

        if (!$this->poster_path || !file_exists($file)) {
            return "{$url}/../placeholders/poster-placeholder.jpg";
        }

        return "{$url}/{$this->poster_path}";
    }


    // VERIFICAR SE PODE SER EDITADO
    public function isEditable() {
        return true;
    }

    // VERIFICAR SE PODE SER ELIMINADO
    public function isDeletable(): bool {
        return !$this->getSessoes()->exists();
    }


    // OBTER GÉNEROS DO FILME
    public function afterFind()
    {
        parent::afterFind();
        $this->generosSelecionados = ArrayHelper::getColumn($this->generos, 'id');
    }

    // GUARDAR GÉNEROS NA TABLE FILME_GÉNERO
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // remover todos os géneros anteriores
        $this->unlinkAll('generos', true);

        // adicionar os novos
        if (is_array($this->generosSelecionados)) {
            foreach ($this->generosSelecionados as $generoId) {
                $genero = Genero::findOne($generoId);
                if ($genero) {
                    $this->link('generos', $genero);
                }
            }
        }
    }

    /**
     * Gets query for [[Generos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGeneros()
    {
        return $this->hasMany(Genero::class, ['id' => 'genero_id'])
            ->viaTable('filme_genero', ['filme_id' => 'id']);
    }

    /**
     * Gets query for [[Sessões]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessoes()
    {
        return $this->hasMany(Sessao::class, ['filme_id' => 'id']);
    }

    public static function OptsRatingKids()
    {
        return [
            self::RATING_TODOS,
            self::RATING_M3,
            self::RATING_M6
        ];
    }

    /**
     * column rating ENUM value labels
     * @return string[]
     */
    public static function optsRating()
    {
        return [
            self::RATING_TODOS => 'Todos',
            self::RATING_M3 => 'M3',
            self::RATING_M6 => 'M6',
            self::RATING_M12 => 'M12',
            self::RATING_M14 => 'M14',
            self::RATING_M16 => 'M16',
            self::RATING_M18 => 'M18',
        ];
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_BREVEMENTE => 'Brevemente',
            self::ESTADO_EM_EXIBICAO => 'Em exibição',
            self::ESTADO_TERMINADO => 'Terminado',
        ];
    }

    /**
     * @return string
     */
    public function displayRating()
    {
        return self::optsRating()[$this->rating];
    }

    /**
     * @return bool
     */
    public function isRatingTodos()
    {
        return $this->rating === self::RATING_TODOS;
    }

    public function setRatingToTodos()
    {
        $this->rating = self::RATING_TODOS;
    }

    /**
     * @return bool
     */
    public function isRatingM3()
    {
        return $this->rating === self::RATING_M3;
    }

    public function setRatingToM3()
    {
        $this->rating = self::RATING_M3;
    }

    /**
     * @return bool
     */
    public function isRatingM6()
    {
        return $this->rating === self::RATING_M6;
    }

    public function setRatingToM6()
    {
        $this->rating = self::RATING_M6;
    }

    /**
     * @return bool
     */
    public function isRatingM12()
    {
        return $this->rating === self::RATING_M12;
    }

    public function setRatingToM12()
    {
        $this->rating = self::RATING_M12;
    }

    /**
     * @return bool
     */
    public function isRatingM14()
    {
        return $this->rating === self::RATING_M14;
    }

    public function setRatingToM14()
    {
        $this->rating = self::RATING_M14;
    }

    /**
     * @return bool
     */
    public function isRatingM16()
    {
        return $this->rating === self::RATING_M16;
    }

    public function setRatingToM16()
    {
        $this->rating = self::RATING_M16;
    }

    /**
     * @return bool
     */
    public function isRatingM18()
    {
        return $this->rating === self::RATING_M18;
    }

    public function setRatingToM18()
    {
        $this->rating = self::RATING_M18;
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
    public function isEstadoBrevemente()
    {
        return $this->estado === self::ESTADO_BREVEMENTE;
    }

    public function setEstadoToBrevemente()
    {
        $this->estado = self::ESTADO_BREVEMENTE;
    }

    /**
     * @return bool
     */
    public function isEstadoEmExibicao()
    {
        return $this->estado === self::ESTADO_EM_EXIBICAO;
    }

    public function setEstadoToEmExibicao()
    {
        $this->estado = self::ESTADO_EM_EXIBICAO;
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
