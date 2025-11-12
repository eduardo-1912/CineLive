<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sessao;

/**
 * SessaoSearch represents the model behind the search form of `common\models\Sessao`.
 */
class SessaoSearch extends Sessao
{
    public $tituloFilme;
    public $numeroSala;
    public $estado;
    public $lugaresDisponiveis;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'filme_id', 'sala_id', 'cinema_id'], 'integer'],
            [['data', 'hora_inicio', 'hora_fim', 'tituloFilme', 'numeroSala', 'lugaresDisponiveis', 'estado'], 'safe'],
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
        $query = Sessao::find()->joinWith(['filme', 'sala', 'cinema']);

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

        $this->load($params);

        // PERMITIR ORDENAR POR NOME DO CLIENTE
        $dataProvider->sort->attributes['estado'] = [
            'asc' => ['estado' => SORT_ASC],
            'desc' => ['estado' => SORT_DESC],
        ];

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'data' => $this->data,
            'hora_inicio' => $this->hora_inicio,
            'hora_fim' => $this->hora_fim,
            'filme_id' => $this->filme_id,
            'sessao.sala_id' => $this->sala,
            'sessao.cinema_id' => $this->cinema_id,
        ]);

        $query->andFilterWhere(['like', 'filme.titulo', $this->tituloFilme]);
        $query->andFilterWhere(['like', 'sala.numero', $this->numeroSala]);

        // OBTER TODAS AS SESSÕES
        $sessoes = $query->all();

        // FILTRO POR ESTADO
        if (!empty($this->estado)) {
            $sessoes = array_filter($sessoes, function ($sessao) {
                return $sessao->estado === $this->estado;
            });
        }

        // FILTRO POR LUGARES DISPONÍVEIS
        if (!empty($this->lugaresDisponiveis)) {
            $sessoes = array_filter($sessoes, function ($sessao) {
                return $sessao->numeroLugaresDisponiveis == $this->lugaresDisponiveis;
            });
        }

        // ORDENAR POR ESTADO
        $ordemEstados = [
            Sessao::ESTADO_A_DECORRER => 1,
            Sessao::ESTADO_ATIVA => 2,
            Sessao::ESTADO_ESGOTADA => 3,
            Sessao::ESTADO_TERMINADA => 4,
        ];

        usort($sessoes, function ($a, $b) use ($ordemEstados) {
            return $ordemEstados[$a->estado] <=> $ordemEstados[$b->estado];
        });

        $dataProvider->setModels($sessoes);

        return $dataProvider;
    }
}
