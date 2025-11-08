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
    public $lugares;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'cinema_id', 'numero', 'num_filas', 'num_colunas', 'lugares'], 'integer'],
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
            'id' => $this->id,
            'cinema_id' => $this->cinema_id,
            'numero' => $this->numero,
            'num_filas' => $this->num_filas,
            'num_colunas' => $this->num_colunas,
            'preco_bilhete' => $this->preco_bilhete,
        ]);

        $query->andFilterWhere(['like', 'estado', $this->estado]);

        // FILTRAR POR NÚMERO DE LUGARES
        if (!empty($this->lugares)) {
            $query->andWhere('(num_filas * num_colunas) = :lugares', [':lugares' => $this->lugares]);
        }

        return $dataProvider;
    }
}
