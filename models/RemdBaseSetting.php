<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "remd_base_setting". Содержит базовые настройки системы.
 *
 * @property int $id Уникальный идентификатор настроек
 * @property string|null $date_from Начальная дата периода в формате d.m.Y
 * @property string|null $date_to Конечная дата периода в формате d.m.Y
 * @property string|null $date_of_update Дата последнего обновления в формате d.m.Y
 * @property int|null $page_size Количество элементов на странице (от 5 до 300)
 * @property int|null $lk_document_filter_enabled Флаг активации фильтра документов в ЛК (0 или 1)
 * @property int|null $created_at Временная метка создания записи
 * @property int|null $updated_at Временная метка обновления записи
 */

class RemdBaseSetting extends ActiveRecord
{
    /**
     * Возвращает имя таблицы в базе данных, связанной с этой моделью.
     *
     * @return string Название таблицы в базе данных
     */
    public static function tableName()
    {
        return 'remd_base_setting';
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
            [['date_from', 'date_to', 'date_of_update'], 'date', 'format' => 'php:d.m.Y'],
            [['page_size'], 'integer', 'min' => 5, 'max' => 300],
            [['lk_document_filter_enabled'], 'boolean'],
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
            'date_from' => 'Дата начала периода',
            'date_to' => 'Дата окончания периода',
            'date_of_update' => 'Дата последнего обновления',
            'page_size' => 'Количество элементов на странице',
            'lk_document_filter_enabled' => 'Учитывать настройки по типам документов в личном кабинете сотрудника',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * Получает текущие настройки системы.
     * Если настройки не существуют, создает новую запись с id = 1.
     *
     * @return RemdBaseSetting|null Объект настроек или null в случае ошибки
     */
    public static function getSettings()
    {
        $settings = static::findOne(1);
        if (!$settings) {
            $settings = new static();
            $settings->id = 1;
        }
        return $settings;
    }

    /**
     * Выполняется перед сохранением модели.
     * Конвертирует даты из формата d.m.Y в Y-m-d для хранения в БД.
     *
     * @param bool $insert Флаг, указывающий выполняется вставка новой записи или обновление
     * @return bool Возвращает true, если операция должна быть продолжена
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!empty($this->date_from)) {
                $this->date_from = \DateTime::createFromFormat('d.m.Y', $this->date_from)->format('Y-m-d');
            }
            if (!empty($this->date_to)) {
                $this->date_to = \DateTime::createFromFormat('d.m.Y', $this->date_to)->format('Y-m-d');
            }
            if (!empty($this->date_of_update)) {
                $this->date_of_update = \DateTime::createFromFormat('d.m.Y', $this->date_of_update)->format('Y-m-d');
            }
            return true;
        }
        return false;
    }

    /**
     * Выполняется после выборки данных из БД.
     * Конвертирует даты из формата Y-m-d в d.m.Y для отображения.
     */
    public function afterFind()
    {
        parent::afterFind();
        if (!empty($this->date_from)) {
            $this->date_from = \DateTime::createFromFormat('Y-m-d', $this->date_from)->format('d.m.Y');
        }
        if (!empty($this->date_to)) {
            $this->date_to = \DateTime::createFromFormat('Y-m-d', $this->date_to)->format('d.m.Y');
        }
        if (!empty($this->date_of_update)) {
            $this->date_of_update = \DateTime::createFromFormat('Y-m-d', $this->date_of_update)->format('d.m.Y');
        }
    }
}
