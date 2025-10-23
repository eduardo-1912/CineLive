<?php

namespace common\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "filme".
 *
 * @property int $id
 * @property string $titulo
 * @property string $sinopse
 * @property int $duracao
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

    /**
     * ENUM field values
     */
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
            [['titulo', 'sinopse', 'duracao', 'estreia', 'idioma', 'realizacao', 'trailer_url', 'estado'], 'required'],
            [['sinopse', 'estado'], 'string'],
            [['duracao'], 'integer'],
            [['estreia'], 'safe'],
            [['titulo', 'trailer_url', 'poster_path'], 'string', 'max' => 255],
            [['idioma'], 'string', 'max' => 50],
            [['realizacao'], 'string', 'max' => 80],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            ['posterFile', 'file', 'skipOnEmpty' => true,
                'extensions' => ['png','jpg','jpeg','webp'],
                'maxSize' => 5 * 1024 * 1024, // 5MB
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
            'estreia' => 'Estreia',
            'idioma' => 'Idioma',
            'realizacao' => 'Realização',
            'trailer_url' => 'Trailer',
            'poster_path' => 'Poster',
            'estado' => 'Estado',
            'posterFile' => 'Carregar Poster',
        ];
    }

    /**
     * Gets query for [[FilmeGeneros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilmeGeneros()
    {
        return $this->hasMany(FilmeGenero::class, ['filme_id' => 'id']);
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
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_BREVEMENTE => 'brevemente',
            self::ESTADO_EM_EXIBICAO => 'em_exibicao',
            self::ESTADO_TERMINADO => 'terminado',
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
    public function isEstadoEmexibicao()
    {
        return $this->estado === self::ESTADO_EM_EXIBICAO;
    }

    public function setEstadoToEmexibicao()
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

    public function getPosterUrl(): string
    {
        // Devolver caminho do poster
        if ($this->poster_path) {
            return Yii::$app->params['posterUrl'] . '/' . ltrim($this->poster_path, '/');
        }
        // Fallback (placeholder se não tiver imagem do poster)
        return '/images/placeholders/poster-placeholder.jpg';
    }

}
