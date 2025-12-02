<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Compra;

/**
 * CompraSearch represents the model behind the search form of `common\models\Compra`.
 */
class CompraSearch extends Compra
{
    public $nomeCliente;
    public $cinema_id;
    public $total;
    public $numeroBilhetes;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'cliente_id', 'sessao_id', 'cinema_id', 'numeroBilhetes'], 'integer'],
            [['total'], 'number'],
            [['data', 'pagamento', 'estado', 'nomeCliente'], 'safe'],
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
        $query = Compra::find()->joinWith(['cliente.profile', 'sessao.cinema', 'bilhetes']);
        $query->groupBy('compra.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => ['data' => SORT_DESC],
            ],
        ]);

        // PERMITIR ORDENAR POR NOME DO CLIENTE
        $dataProvider->sort->attributes['nomeCliente'] = [
            'asc' => ['user.username' => SORT_ASC],
            'desc' => ['user.username' => SORT_DESC],
        ];

        // PERMITIR ORDENAR POR NÚMERO DE BILHETES
        $dataProvider->sort->attributes['numeroBilhetes'] = [
            'asc' => ['COUNT(bilhete.id)' => SORT_ASC],
            'desc' => ['COUNT(bilhete.id)' => SORT_DESC],
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
            'cliente_id' => $this->cliente_id,
            'compra.sessao_id' => $this->sessao_id,
            'data' => $this->data,
        ]);

        $query->andFilterWhere(['like', 'pagamento', $this->pagamento])
            ->andFilterWhere(['like', 'compra.estado', $this->estado])
            ->andFilterWhere(['like', 'user_profile.nome', $this->nomeCliente])
            ->andFilterWhere(['cinema.id' => $this->cinema_id]);

        // FILTRAR POR TOTAL
        $query->andFilterHaving(['>=', 'SUM(bilhete.preco)', $this->total]);

        // FILTRAR POR NÚMERO DE BILHETES
        if (!empty($this->numeroBilhetes)) {
            $query->having(['=', 'COUNT(bilhete.id)', $this->numeroBilhetes]);
        }

        return $dataProvider;
    }
}
