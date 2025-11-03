<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Filme;

/**
 * FilmeSearch represents the model behind the search form of `common\models\Filme`.
 */
class FilmeSearch extends Filme
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'duracao'], 'integer'],
            [['titulo', 'sinopse', 'rating', 'estreia', 'idioma', 'realizacao', 'trailer_url', 'poster_path', 'estado'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Filme::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'duracao' => $this->duracao,
            'estreia' => $this->estreia,
        ]);

        $query->andFilterWhere(['like', 'titulo', $this->titulo])
            ->andFilterWhere(['like', 'sinopse', $this->sinopse])
            ->andFilterWhere(['like', 'idioma', $this->idioma])
            ->andFilterWhere(['like', 'realizacao', $this->realizacao])
            ->andFilterWhere(['like', 'trailer_url', $this->trailer_url])
            ->andFilterWhere(['like', 'poster_path', $this->poster_path])
            ->andFilterWhere(['like', 'estado', $this->estado])
            ->andFilterWhere(['rating' => $this->rating]);

        return $dataProvider;
    }
}
