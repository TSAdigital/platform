<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CertificateSearch represents the model behind the search form of `app\models\Certificate`.
 */
class CertificateSearch extends Certificate
{
    public $pageSize = 10;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            ['employee_id', 'string'],
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
        $query = Certificate::find()->joinWith('employee');

        $this->pageSize = (isset($params['pageSize'])  && $params['pageSize'] > 0 && $params['pageSize'] <= 100) ? intval($params['pageSize']) : $this->pageSize;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id' => [
                        'asc' => ['certificate.id' => SORT_ASC],
                        'desc' => ['certificate.id' => SORT_DESC],
                        'label' => 'id',
                    ],
                    'employee_id' => [
                        'asc' => ['employee.last_name' => SORT_ASC, 'certificate.id' => SORT_ASC],
                        'desc' => ['employee.last_name' => SORT_DESC, 'certificate.id' => SORT_ASC],
                        'label' => 'employee_id',
                    ],
                    'status' => [
                        'asc' => ['certificate.status' => SORT_ASC, 'certificate.id' => SORT_ASC],
                        'desc' => ['certificate.status' => SORT_DESC, 'certificate.id' => SORT_ASC],
                        'label' => 'status',
                    ],
                ],
                'defaultOrder' => [
                    'id' => SORT_DESC,
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
            'id' => $this->id,
            'certificate.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name)', $this->employee_id]);
        $query->andFilterWhere(['like', 'serial_number', $this->serial_number]);

        return $dataProvider;
    }
}
