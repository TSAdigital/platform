<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User
{
    public $pageSize = 10;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'integer'],
            [['username', 'role'], 'string'],
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
        $query = User::find();

        $this->pageSize = (isset($params['pageSize'])  && $params['pageSize'] > 0 && $params['pageSize'] <= 100) ? intval($params['pageSize']) : $this->pageSize;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'username' => [
                        'asc' => ['user.username' => SORT_ASC, 'user.id' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC, 'user.id' => SORT_ASC],
                        'label' => 'Имя пользователя',
                    ],
                    'status' => [
                        'asc' => ['user.status' => SORT_ASC, 'user.username' => SORT_ASC],
                        'desc' => ['user.status' => SORT_DESC, 'user.username' => SORT_ASC],
                        'label' => 'Статус',
                    ],
                    'role' => [
                        'asc' => ['auth_assignment.item_name' => SORT_ASC, 'user.username' => SORT_ASC],
                        'desc' => ['auth_assignment.item_name' => SORT_DESC, 'user.username' => SORT_ASC],
                        'label' => 'Роль',
                    ],
                ],
                'defaultOrder' => [
                    'username' => SORT_ASC,
                ],
            ],
            'pagination' => [
                'pageSize' => $this->pageSize,
            ],
        ]);

        $this->load($params);

        $query->joinWith('authAssignments.itemName');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['auth_assignment.item_name' => $this->role]);

        return $dataProvider;
    }
}
