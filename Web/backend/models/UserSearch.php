<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

class UserSearch extends User
{
    public $nome;
    public $telemovel;
    public $role;
    public $cinema_id;

    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at', 'cinema_id'], 'integer'],
            [['username', 'email', 'nome', 'telemovel', 'role'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = User::find()
            ->joinWith(['profile', 'cinema']); // Faz join com user_profile e cinema

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'username',
                    'email',
                    'nome' => [
                        'asc' => ['user_profile.nome' => SORT_ASC],
                        'desc' => ['user_profile.nome' => SORT_DESC],
                    ],
                    'telemovel' => [
                        'asc' => ['user_profile.telemovel' => SORT_ASC],
                        'desc' => ['user_profile.telemovel' => SORT_DESC],
                    ],
                    'cinema_id' => [
                        'asc' => ['cinema.nome' => SORT_ASC],
                        'desc' => ['cinema.nome' => SORT_DESC],
                    ],
                    'status',
                    'created_at',
                ],
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere(['user.id' => $this->id])
            ->andFilterWhere(['user.status' => $this->status])
            ->andFilterWhere(['user_profile.cinema_id' => $this->cinema_id])
            ->andFilterWhere(['like', 'user.username', $this->username])
            ->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['like', 'user_profile.nome', $this->nome])
            ->andFilterWhere(['like', 'user_profile.telemovel', $this->telemovel]);

        // FILTRAR POR ROLE
        if ($this->role) {
            $ids = Yii::$app->authManager->getUserIdsByRole($this->role);
            $query->andWhere(['user.id' => $ids ?: 0]);
        }

        return $dataProvider;
    }
}
