<?php

namespace common\models;

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
            [['filme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Filme::class, 'targetAttribute' => ['filme_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
            [['cinema_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cinema::class, 'targetAttribute' => ['cinema_id' => 'id']],
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
            'hora_inicio' => 'Hora Inicio',
            'hora_fim' => 'Hora Fim',
            'filme_id' => 'Filme ID',
            'sala_id' => 'Sala ID',
            'cinema_id' => 'Cinema ID',
        ];
    }

    /**
     * Gets query for [[Bilhetes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBilhetes()
    {
        return $this->hasMany(Bilhete::class, ['sessao_id' => 'id']);
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
