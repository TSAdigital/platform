<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "remd_employee".
 * Связующая таблица между документами РЕМД и сотрудниками (многие-ко-многим).
 *
 * @property int $remd_id ID связанного документа РЕМД
 * @property int $employee_id ID связанного сотрудника
 * @property int $created_at Временная метка создания связи
 * @property int|null $updated_at Временная метка обновления связи
 *
 * @property Remd $remd Связанный документ РЕМД
 * @property Employee $employee Связанный сотрудник
 */

class RemdEmployee extends ActiveRecord
{
    /**
     * Возвращает имя таблицы в базе данных, связанной с этой моделью.
     *
     * @return string Название таблицы в базе данных
     */
    public static function tableName()
    {
        return 'remd_employee';
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
            [['remd_id', 'employee_id'], 'integer'],
            [['remd_id', 'employee_id'], 'required'],
            [['remd_id', 'employee_id'], 'unique', 'targetAttribute' => ['remd_id', 'employee_id']],
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
            'remd_id' => 'Ремд',
            'employee_id' => 'Сотрудник',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * Выполняется перед сохранением модели.
     * Устанавливает временную метку создания для новых записей.
     *
     * @param bool $insert Флаг, указывающий выполняется вставка новой записи или обновление
     * @return bool Возвращает true, если операция должна быть продолжена
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = time();
            }
            return true;
        }
        return false;
    }

    /**
     * Возвращает связь с документом РЕМД.
     *
     * @return ActiveQuery Запрос для получения связанного документа РЕМД
     */
    public function getRemd()
    {
        return $this->hasOne(Remd::class, ['id' => 'remd_id']);
    }

    /**
     * Возвращает связь с сотрудником.
     *
     * @return ActiveQuery Запрос для получения связанного сотрудника
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }
}
