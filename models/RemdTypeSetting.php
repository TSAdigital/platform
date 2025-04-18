<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Модель для таблицы "remd_type_setting".
 * Хранит настройки доступных типов документов РЕМД в системе.
 *
 * @property int $id Уникальный идентификатор настроек
 * @property string|null $enabled_doc_types JSON-строка с массивом разрешенных типов документов
 * @property int|null $created_at Временная метка создания записи
 * @property int|null $updated_at Временная метка обновления записи
 */

class RemdTypeSetting extends ActiveRecord
{
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
     * Возвращает имя таблицы в базе данных, связанной с этой моделью.
     *
     * @return string Название таблицы в базе данных
     */
    public static function tableName()
    {
        return 'remd_type_setting';
    }

    /**
     * Возвращает правила валидации для атрибутов модели.
     *
     * @return array Массив правил валидации
     */
    public function rules()
    {
        return [
            [['enabled_doc_types'], 'string'],
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
            'enabled_doc_types' => 'Доступные типы документов',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * Получает текущие настройки типов документов.
     * Если настройки не существуют, создает новую запись.
     *
     * @return RemdTypeSetting|null Объект настроек или null в случае ошибки
     * @throws Exception Если произошла ошибка при сохранении новой записи
     */
    public static function getSettings()
    {
        $settings = self::findOne(1);

        if (!$settings) {
            $settings = new self();
            $settings->save();
        }

        return $settings;
    }

    /**
     * Возвращает массив разрешенных типов документов.
     *
     * @return array Массив разрешенных типов документов или пустой массив, если типы не заданы
     */
    public function getEnabledDocTypesArray()
    {
        return $this->enabled_doc_types ? json_decode($this->enabled_doc_types, true) : [];
    }

    /**
     * Устанавливает разрешенные типы документов.
     *
     * @param array $types Массив типов документов для сохранения
     */
    public function setEnabledDocTypesArray($types)
    {
        $this->enabled_doc_types = json_encode($types);
    }
}
