<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Bilhete;

/**
 * BilheteSearch represents the model behind the search form of `common\models\Bilhete`.
 */
class BilheteSearch extends Bilhete
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'compra_id', 'sessao_id'], 'integer'],
            [['lugar', 'codigo', 'estado'], 'safe'],
            [['preco'], 'number'],
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
        $query = Bilhete::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'compra_id' => $this->compra_id,
            'sessao_id' => $this->sessao_id,
            'preco' => $this->preco,
        ]);

        $query->andFilterWhere(['like', 'lugar', $this->lugar])
            ->andFilterWhere(['like', 'codigo', $this->codigo])
            ->andFilterWhere(['like', 'estado', $this->estado]);

        return $dataProvider;
    }
}
