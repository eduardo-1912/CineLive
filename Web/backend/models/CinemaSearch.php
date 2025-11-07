<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Cinema;

/**
 * CinemaSearch represents the model behind the search form of `common\models\Cinema`.
 */
class CinemaSearch extends Cinema
{
    public $morada;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'telefone', 'gerente_id'], 'integer'],
            [['nome', 'rua', 'codigo_postal', 'cidade', 'email', 'horario_abertura', 'horario_fecho', 'estado'], 'safe'],
            [['latitude', 'longitude'], 'number'],
            [['morada'], 'safe'],
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
        $query = Cinema::find();

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
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'telefone' => $this->telefone,
            'horario_abertura' => $this->horario_abertura,
            'horario_fecho' => $this->horario_fecho,
            'estado' => $this->estado,
            'gerente_id' => $this->gerente_id,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['or',
                ['like', 'rua', $this->morada],
                ['like', 'codigo_postal', $this->morada],
                ['like', 'cidade', $this->morada],
            ]);

        return $dataProvider;
    }
}
