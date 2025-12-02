<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sala;

/**
 * SalaSearch represents the model behind the search form of `common\models\Sala`.
 */
class SalaSearch extends Sala
{
    public $numeroLugares;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'cinema_id', 'numero', 'num_filas', 'num_colunas', 'numeroLugares'], 'integer'],
            [['preco_bilhete'], 'number'],
            [['estado'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Sala::find()->orderBy(['cinema_id' => SORT_ASC,'sala.numero' => SORT_ASC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'sala.id' => $this->id,
            'sala.cinema_id' => $this->cinema_id,
            'sala.numero' => $this->numero,
            'sala.num_filas' => $this->num_filas,
            'sala.num_colunas' => $this->num_colunas,
            'sala.preco_bilhete' => $this->preco_bilhete,
        ]);

        $query->andFilterWhere(['like', 'sala.estado', $this->estado]);

        if ($this->numeroLugares) {
            $query->andWhere('num_filas * num_colunas = :lugares', [':lugares' => $this->numeroLugares]);
        }

        return $dataProvider;
    }
}
