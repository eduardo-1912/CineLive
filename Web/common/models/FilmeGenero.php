<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "filme_genero".
 *
 * @property int $filme_id
 * @property int $genero_id
 *
 * @property Filme $filme
 * @property Genero $genero
 */
class FilmeGenero extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'filme_genero';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['filme_id', 'genero_id'], 'required'],
            [['filme_id', 'genero_id'], 'integer'],
            [['filme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Filme::class, 'targetAttribute' => ['filme_id' => 'id']],
            [['genero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Genero::class, 'targetAttribute' => ['genero_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'filme_id' => 'Filme ID',
            'genero_id' => 'Genero ID',
        ];
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
     * Gets query for [[Genero]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenero()
    {
        return $this->hasOne(Genero::class, ['id' => 'genero_id']);
    }

}
