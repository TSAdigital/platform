<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentSearch represents the model behind the search form of `app\models\Document`.
 */
class DocumentSearch extends Document
{
    public $pageSize = 10;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'integer'],
            ['name', 'string', 'max' => 255],
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
        $query = Document::find()->joinWith(['createdBy', 'accessRecords', 'documentRead']);;

        $user_id = Yii::$app->user->id;

        $query->where([
            'or',
            ['and',
                ['document.status' => Document::STATUS_DRAFT],
                ['document.created_by' => $user_id]
            ],
            ['and',
                ['!=', 'document.status', Document::STATUS_DRAFT],
                ['or',
                    ['document_access.user_id' => $user_id],
                    ['document.created_by' => $user_id]
                ]
            ]
        ])->groupBy('document.id');

        // add conditions that should always apply here

        $this->pageSize = (isset($params['pageSize'])  && $params['pageSize'] > 0 && $params['pageSize'] <= 100) ? intval($params['pageSize']) : $this->pageSize;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id' => [
                        'asc' => ['id' => SORT_ASC],
                        'desc' => ['id' => SORT_DESC],
                        'label' => 'id',
                    ],
                    'name' => [
                        'asc' => ['name' => SORT_ASC, 'id' => SORT_ASC],
                        'desc' => ['name' => SORT_DESC, 'id' => SORT_ASC],
                        'label' => 'name',
                    ],
                    'status' => [
                        'asc' => ['status' => SORT_ASC, 'name' => SORT_ASC],
                        'desc' => ['status' => SORT_DESC, 'name' => SORT_ASC],
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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
