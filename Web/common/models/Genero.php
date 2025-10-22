<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "genero".
 *
 * @property int $id
 * @property string $nome
 *
 * @property FilmeGenero[] $filmeGeneros
 */
class Genero extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'genero';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome'], 'required'],
            [['nome'], 'string', 'max' => 80],
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
        ];
    }

    /**
     * Gets query for [[FilmeGeneros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilmeGeneros()
    {
        return $this->hasMany(FilmeGenero::class, ['genero_id' => 'id']);
    }

}
