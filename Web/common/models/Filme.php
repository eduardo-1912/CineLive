<?php

namespace common\models;

use Exception;
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
 * @property FilmeGenero[] $filmeGeneros
 * @property Sessao[] $sessaos
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

    public function getPosterUrl(): string
    {
        // Caminhos definidos em common/config/params.php
        $posterDir = Yii::getAlias(Yii::$app->params['posterPath']); // caminho físico absoluto
        $posterUrlBase = Yii::$app->params['posterUrl']; // URL público acessível via browser

        // Caminho absoluto completo (ficheiro físico no servidor)
        $posterFile = rtrim($posterDir, '/') . '/' . ltrim($this->poster_path, '/');

        // Caminho público (para o <img src="...">)
        $posterUrl = rtrim($posterUrlBase, '/') . '/' . ltrim($this->poster_path, '/');

        // Placeholder público (dentro da mesma pasta 'uploads')
        $placeholderUrl = rtrim($posterUrlBase, '/') . '/../placeholders/poster-placeholder.jpg';

        // Se não tiver poster_path definido → devolve placeholder
        if (empty($this->poster_path)) {
            return $placeholderUrl;
        }

        // Se o ficheiro não existir fisicamente → devolve placeholder
        if (!file_exists($posterFile)) {
            return $placeholderUrl;
        }

        // Caso contrário → devolve URL do poster
        return $posterUrl;
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
            'rating' => 'Rating',
            'estreia' => 'Estreia',
            'estreiaFormatada' => 'Estreia',
            'idioma' => 'Idioma',
            'realizacao' => 'Realização',
            'trailer_url' => 'Trailer',
            'poster_path' => 'Poster',
            'estado' => 'Estado',
            'posterFile' => 'Poster',
            'generosSelecionados' => 'Géneros',
        ];
    }

    /**
     * Gets query for [[FilmeGeneros]].
     *
     * @return \yii\db\ActiveQuery
     */

    // VERIFICAR SE PODE SER EDITADO
    public function isEditable(): bool { return true; }

    // VERIFICAR SE PODE SER ELIMINADO
    public function isDeletable(): bool { return !$this->getSessaos()->exists(); }

    public function getFilmeGeneros()
    {
        return $this->hasMany(FilmeGenero::class, ['filme_id' => 'id']);
    }

    public function getGeneros()
    {
        return $this->hasMany(Genero::class, ['id' => 'genero_id'])->via('filmeGeneros');
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

        // REMOVER GÉNEROS ANTIGOS
        FilmeGenero::deleteAll(['filme_id' => $this->id]);

        // ADICIONAR GÉNEROS NOVOS
        if (is_array($this->generosSelecionados)) {
            foreach ($this->generosSelecionados as $generoId) {
                $filmeGenero = new FilmeGenero();
                $filmeGenero->filme_id = $this->id;
                $filmeGenero->genero_id = $generoId;
                $filmeGenero->save();
            }
        }
    }

    // VERIFICAR SE TEM SESSÕES ATIVAS
    public function hasSessoesAtivas(): bool
    {
        foreach ($this->sessaos as $sessao) {
            $estado = $sessao->getEstado();

            if ($estado === Sessao::ESTADO_ATIVA) {
                return true;
            }
        }

        return false;
    }


    // OBTER ESTREIA FORMATADA (DD/MM/AAAA)
    public function getEstreiaFormatada(): string
    {
        return Yii::$app->formatter->asDate($this->estreia, 'php:d/m/Y');
    }

    // OBTER DURAÇÃO EM HORAS
    public function getDuracaoEmHoras()
    {
        if (!$this->duracao || $this->duracao <= 0) {
            return '-';
        }

        $horas = floor($this->duracao / 60);
        $minutos = $this->duracao % 60;

        if ($horas > 0) {
            return sprintf('%dh %02dmin', $horas, $minutos);
        }
        return sprintf('%dmin', $minutos);
    }

    // OBTER FILMES EM EXIBIÇÃO POR CINEMA
    public static function getFilmesEmExibicaoPorCinema($cinemaId)
    {
        $now = date('Y-m-d');

        return self::find()
            ->alias('f')
            ->joinWith('sessaos s')
            ->where([
                's.cinema_id' => $cinemaId,
            ])
            ->andWhere(['>=', 's.data', $now])
            ->distinct()
            ->orderBy(['f.titulo' => SORT_ASC])
            ->all();
    }

    // OBTER CINEMAS COM SESSÕES FUTURAS PARA O DETERMINADO FILME
    public function getCinemasComSessoesFuturas()
    {
        return Cinema::find()
            ->alias('c')
            ->joinWith('sessaos s')
            ->where(['s.filme_id' => $this->id, 'c.estado' => Cinema::ESTADO_ATIVO])
            ->andWhere(['>=', 's.data', date('Y-m-d')])
            ->distinct()
            ->orderBy(['c.nome' => SORT_ASC])
            ->all();
    }

    // VERIFICAR SE O RATING É PARA CRIANÇAS
    public static function ratingsKids()
    {
        return [self::RATING_TODOS, self::RATING_M3, self::RATING_M6];
    }

    public function isRatingKids()
    {
        return ($this->isRatingTodos() || $this->isRatingM3() || $this->isRatingM6());
    }

    /**
     * Gets query for [[Sessaos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessaos()
    {
        return $this->hasMany(Sessao::class, ['filme_id' => 'id']);
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
