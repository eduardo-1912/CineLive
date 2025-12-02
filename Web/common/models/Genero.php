<?php

namespace common\models;

/**
 * This is the model class for table "genero".
 *
 * @property int $id
 * @property string $nome
 *
 * @property Filme[] $filmes
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
            [['nome'], 'unique'],
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

    public function isDeletable(): bool
    {
        return true;
    }

    /**
     * Gets query for [[Filmes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFilmes()
    {
        return $this->hasMany(Filme::class, ['id' => 'filme_id'])
            ->viaTable('filme_genero', ['genero_id' => 'id']);
    }
}
