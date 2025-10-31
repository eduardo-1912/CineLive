<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserExtension;

class UserSearch extends UserExtension
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
        $query = UserExtension::find()
            ->joinWith(['profile', 'cinema']); // Faz join com user_profile e cinema

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
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

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['user.id' => $this->id])
            ->andFilterWhere(['user.status' => $this->status])
            ->andFilterWhere(['user_profile.cinema_id' => $this->cinema_id])
            ->andFilterWhere(['like', 'user.username', $this->username])
            ->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['like', 'user_profile.nome', $this->nome])
            ->andFilterWhere(['like', 'user_profile.telemovel', $this->telemovel]);

        // FILTRAR POR ROLE
        if (!empty($this->role)) {
            // OBTER IDS DOS UTILIZADORES COM O ROLE SELECIONADO NO FILTRO DROPDOWN
            $userIds = Yii::$app->authManager->getUserIdsByRole($this->role);
            $query->andFilterWhere(['user.id' => $userIds]);
        }

        return $dataProvider;
    }
}
