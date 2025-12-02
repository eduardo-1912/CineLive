<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AluguerSala;

/**
 * AluguerSalaSearch represents the model behind the search form of `common\models\AluguerSala`.
 */
class AluguerSalaSearch extends AluguerSala
{
    public $nomeCliente;
    public $numeroSala;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'cliente_id', 'cinema_id', 'sala_id', 'numeroSala'], 'integer'],
            [['data', 'hora_inicio', 'hora_fim', 'estado', 'tipo_evento', 'observacoes', 'nomeCliente'], 'safe'],
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
        $query = AluguerSala::find()->joinWith(['cliente.profile', 'cinema', 'sala']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => ['estado' => SORT_ASC],
            ],
        ]);

        // PERMITIR ORDENAR POR NOME DO CLIENTE
        $dataProvider->sort->attributes['nomeCliente'] = [
            'asc' => ['user.username' => SORT_ASC],
            'desc' => ['user.username' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'sala_id' => $this->sala_id,
            'data' => $this->data,
            'hora_inicio' => $this->hora_inicio,
            'hora_fim' => $this->hora_fim,
        ]);

        $query->andFilterWhere(['like', 'aluguer_sala.estado', $this->estado])
            ->andFilterWhere(['like', 'tipo_evento', $this->tipo_evento])
            ->andFilterWhere(['like', 'observacoes', $this->observacoes])
            ->andFilterWhere(['like', 'user_profile.nome', $this->nomeCliente])
            ->andFilterWhere(['cinema.id' => $this->cinema_id])
            ->andFilterWhere(['sala.id' => $this->numeroSala]);

        return $dataProvider;
    }
}
