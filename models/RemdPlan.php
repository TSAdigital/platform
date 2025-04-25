<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "remd_plan".
 *
 * @property int $id
 * @property int $year
 * @property string $type
 * @property int|null $jan
 * @property int|null $feb
 * @property int|null $mar
 * @property int|null $apr
 * @property int|null $may
 * @property int|null $jun
 * @property int|null $jul
 * @property int|null $aug
 * @property int|null $sep
 * @property int|null $oct
 * @property int|null $nov
 * @property int|null $dec
 * @property int|null $q1
 * @property int|null $q2
 * @property int|null $q3
 * @property int|null $q4
 * @property int|null $year_plan
 * @property int $created_at
 * @property int $updated_at
 */

class RemdPlan extends ActiveRecord
{
    /**
     * Возвращает имя таблицы в базе данных, связанной с этой моделью.
     *
     * @return string Название таблицы в базе данных
     */
    public static function tableName()
    {
        return 'remd_plan';
    }

    /**
     * Возвращает список поведений, которые должны быть прикреплены к этой модели.
     *
     * @return array Массив конфигураций поведений
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Возвращает правила валидации для атрибутов модели.
     *
     * @return array Массив правил валидации
     */
    public function rules()
    {
        return [
            ['year', 'required'],
            ['year', 'integer'],
            ['year', 'trim'],

            ['type', 'string'],

            [['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'q1', 'q2', 'q3', 'q4', 'year_plan'], 'integer'],
            [['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'q1', 'q2', 'q3', 'q4', 'year_plan'], 'required'],
            [['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'q1', 'q2', 'q3', 'q4', 'year_plan'], 'trim'],

            [['year', 'type'], 'unique', 'targetAttribute' => ['year', 'type'], 'message' => 'Комбинация года и вида документа должна быть уникальной.'],
        ];
    }

    /**
     * Возвращает метки атрибутов для отображения.
     *
     * @return array Массив меток атрибутов в формате [атрибут => метка]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'year' => 'Год',
            'type' => 'Вид документа',
            'jan' => 'Январь',
            'feb' => 'Февраль',
            'mar' => 'Март',
            'apr' => 'Апрель',
            'may' => 'Май',
            'jun' => 'Июнь',
            'jul' => 'Июль',
            'aug' => 'Август',
            'sep' => 'Сентябрь',
            'oct' => 'Октябрь',
            'nov' => 'Ноябрь',
            'dec' => 'Декабрь',
            'q1' => '1 квартал',
            'q2' => '2 квартал',
            'q3' => '3 квартал',
            'q4' => '4 квартал',
            'year_plan' => 'Годовой план',
        ];
    }
}
