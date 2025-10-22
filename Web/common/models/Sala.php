<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sala".
 *
 * @property int $id
 * @property int $cinema_id
 * @property int $numero
 * @property int $num_filas
 * @property int $num_colunas
 * @property float $preco_bilhete
 *
 * @property AluguerSala[] $aluguerSalas
 * @property Cinema $cinema
 * @property Sessao[] $sessaos
 */
class Sala extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sala';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cinema_id', 'numero', 'num_filas', 'num_colunas', 'preco_bilhete'], 'required'],
            [['cinema_id', 'numero', 'num_filas', 'num_colunas'], 'integer'],
            [['preco_bilhete'], 'number'],
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
            'cinema_id' => 'Cinema ID',
            'numero' => 'Numero',
            'num_filas' => 'Num Filas',
            'num_colunas' => 'Num Colunas',
            'preco_bilhete' => 'Preco Bilhete',
        ];
    }

    /**
     * Gets query for [[AluguerSalas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAluguerSalas()
    {
        return $this->hasMany(AluguerSala::class, ['sala_id' => 'id']);
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
     * Gets query for [[Sessaos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessaos()
    {
        return $this->hasMany(Sessao::class, ['sala_id' => 'id']);
    }

}
