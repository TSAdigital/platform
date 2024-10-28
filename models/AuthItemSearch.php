<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AuthItemSearch represents the model behind the search form of `app\models\AuthItem`.
 */
class AuthItemSearch extends AuthItem
{
    public $pageSize;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['description', 'safe'],
            ['status', 'integer'],
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
        $query = AuthItem::find()->where(['type' => 1]);

        $this->pageSize = (isset($params['pageSize'])  && $params['pageSize'] > 0 && $params['pageSize'] <= 100) ? intval($params['pageSize']) : $this->pageSize;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'description' => [
                        'asc' => ['description' => SORT_ASC, 'name' => SORT_ASC],
                        'desc' => ['description' => SORT_DESC, 'name' => SORT_ASC],
                        'label' => 'Наименование',
                    ],
                    'status' => [
                        'asc' => ['status' => SORT_ASC, 'description' => SORT_ASC],
                        'desc' => ['status' => SORT_DESC, 'description' => SORT_ASC],
                        'label' => 'Статус',
                    ],
                ],
                'defaultOrder' => [
                    'description' => SORT_ASC,
                ],
            ],
            'pagination' => [
                'pageSize' => $this->pageSize,
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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
