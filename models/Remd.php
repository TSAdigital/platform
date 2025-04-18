<?php

namespace app\models;

use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "remd". Содержит список зарегистрированных документов.
 *
 * @property int $id Идентификатор записи
 * @property string $unique_code Уникальный код документа
 * @property string $type Тип документа
 * @property string $registration_date Дата регистрации документа
 * @property int|null $created_at Временная метка создания записи
 * @property int|null $updated_at Временная метка обновления записи
 */

class Remd extends ActiveRecord
{
    /**
     * Возвращает имя таблицы в базе данных, связанной с этой моделью.
     *
     * @return string Название таблицы в базе данных
     */
    public static function tableName()
    {
        return 'remd';
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
            ['unique_code', 'string', 'max' => 255],
            ['unique_code', 'required'],

            ['type', 'string', 'max' => 255],
            ['type', 'required'],

            ['registration_date', 'date', 'format' => 'php:Y-m-d']
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
            'unique_code' => 'Идентификатор записи',
            'type' => 'Вид документа',
            'registration_date' => 'Дата регистрации',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * Возвращает связь с моделью Employee через промежуточную таблицу remd_employee.
     *
     * @return ActiveQuery Запрос для получения связанных сотрудников
     * @throws InvalidConfigException Если связь настроена неправильно
     */
    public function getEmployees()
    {
        return $this->hasMany(Employee::class, ['id' => 'employee_id'])
            ->viaTable('{{%remd_employee}}', ['remd_id' => 'id']);
    }

    /**
     * Получает самую последнюю дату регистрации из всех документов
     * @return string|null Дата в формате Y-m-d или null, если документов нет
     */
    public static function getLastRegistrationDate()
    {
        return self::find()
            ->select('MAX(registration_date)')
            ->scalar();
    }

    /**
     * Возвращает документы РЕМД сотрудника, сгруппированные по годам, месяцам и типам документов.
     *
     * Метод формирует иерархическую структуру данных, учитывая настройки фильтрации документов.
     * Если в настройках системы включена фильтрация документов, будут возвращены только разрешенные типы документов.
     *
     * @param int $employeeId Идентификатор сотрудника
     * @return array Многомерный массив с группировкой по годам, месяцам и типам документов.
     *               Возвращает пустой массив, если документов не найдено.
     * @throws \Exception При проблемах с обработкой дат документов
     */
    public static function getGroupedByEmployee($employeeId)
    {
        $russianMonths = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март',
            4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
            7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь',
            10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];

        $query = self::find()
            ->joinWith('employees')
            ->where(['employee.id' => $employeeId])
            ->orderBy('registration_date DESC');

        if (RemdBaseSetting::getSettings()->lk_document_filter_enabled &&
            ($types = RemdTypeSetting::getSettings()->getEnabledDocTypesArray())) {
            $query->andWhere(['type' => $types]);
        }

        $documents = $query->all();

        $grouped = [];

        foreach ($documents as $document) {
            $date = new \DateTime($document->registration_date);
            $year = $date->format('Y');
            $month = (int)$date->format('n');
            $monthName = $russianMonths[$month] ?? $date->format('F');
            $type = $document->type;

            if (!isset($grouped[$year])) {
                $grouped[$year] = [
                    'count' => 0,
                    'months' => []
                ];
            }
            $grouped[$year]['count']++;

            if (!isset($grouped[$year]['months'][$month])) {
                $grouped[$year]['months'][$month] = [
                    'name' => $monthName,
                    'count' => 0,
                    'types' => []
                ];
            }
            $grouped[$year]['months'][$month]['count']++;

            if (!isset($grouped[$year]['months'][$month]['types'][$type])) {
                $grouped[$year]['months'][$month]['types'][$type] = 0;
            }
            $grouped[$year]['months'][$month]['types'][$type]++;
        }

        return $grouped;
    }
}
