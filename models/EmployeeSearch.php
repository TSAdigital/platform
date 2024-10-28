<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EmployeeSearch represents the model behind the search form of `app\models\Employee`.
 */
class EmployeeSearch extends Employee
{
    public $full_name;
    public $position_name;
    public $pageSize = 10;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'integer'],
            [['position_name', 'full_name'], 'string'],
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
        $query = Employee::find()->joinWith('position');

        $this->pageSize = (isset($params['pageSize'])  && $params['pageSize'] > 0 && $params['pageSize'] <= 100) ? intval($params['pageSize']) : $this->pageSize;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'full_name' => [
                        'asc' => ['CONCAT(last_name, first_name, middle_name)' => SORT_ASC, 'employee.id' => SORT_ASC],
                        'desc' => ['CONCAT(last_name, first_name, middle_name)' => SORT_DESC, 'employee.id' => SORT_ASC],
                        'label' => 'full_name',
                    ],
                    'position_name' => [
                        'asc' => ['position.name' => SORT_ASC, 'CONCAT(first_name, last_name, middle_name)' => SORT_ASC],
                        'desc' => ['position.name' => SORT_DESC, 'CONCAT(first_name, last_name, middle_name)' => SORT_ASC],
                        'label' => 'position_name',
                    ],
                    'status' => [
                        'asc' => ['status' => SORT_ASC, 'CONCAT(first_name, last_name, middle_name)' => SORT_ASC],
                        'desc' => ['status' => SORT_DESC, 'CONCAT(first_name, last_name, middle_name)' => SORT_ASC],
                        'label' => 'status',
                    ],
                ],
                'defaultOrder' => [
                    'full_name' => SORT_ASC,
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
            'employee.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'CONCAT(last_name, first_name, middle_name)', preg_replace('/[^а-яА-ЯЁё]/u', '', $this->full_name)])
            ->andFilterWhere(['like', 'position.name', $this->position_name]);
        return $dataProvider;
    }
}
