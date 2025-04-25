<?php

namespace app\models;

use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Модель сотрудника организации.
 * Содержит персональные данные, информацию о должности и статусе сотрудника.
 *
 * @property int $id Уникальный идентификатор сотрудника
 * @property string $first_name Имя
 * @property string $last_name Фамилия
 * @property string|null $middle_name Отчество (если имеется)
 * @property string $full_name Полное ФИО (вычисляемое поле)
 * @property string $birth_date Дата рождения в формате d.m.Y
 * @property int $user_id ID связанного пользователя системы
 * @property int $position_id ID должности
 * @property string $position_name Название должности (вычисляемое поле)
 * @property int $status Статус сотрудника (активен/неактивен)
 * @property int $created_by ID пользователя, создавшего запись
 * @property int $updated_by ID пользователя, обновившего запись
 * @property int $created_at Временная метка создания записи
 * @property int $updated_at Временная метка обновления записи
 * @property int $total_documents Всего документов
 *
 * @property Position $position Связанная должность
 * @property User $user Связанный пользователь системы
 * @property Remd[] $remds Связанные документы РЕМД
 */

class Employee extends ActiveRecord
{
    public $total_documents;

    /**
     * Статус: сотрудник активен
     */
    const STATUS_ACTIVE = 1;

    /**
     * Статус: сотрудник неактивен
     */
    const STATUS_INACTIVE = 0;

    /**
     * Возвращает имя таблицы в базе данных, связанной с этой моделью.
     *
     * @return string Название таблицы в базе данных
     */
    public static function tableName()
    {
        return 'employee';
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
            BlameableBehavior::class,
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
            [['first_name', 'last_name'], 'required'],
            [['first_name', 'last_name', 'middle_name'], 'string', 'max' => 255],
            [['first_name', 'last_name', 'middle_name'], 'trim'],

            [['first_name', 'last_name', 'middle_name', 'birth_date'], 'validateUniquePerson'],

            ['birth_date', 'date', 'format' => 'php:d.m.Y'],
            ['birth_date', 'trim'],
            ['birth_date', 'required'],

            ['position_id', 'integer'],
            ['position_id', 'required'],
            ['position_id', 'exist', 'skipOnError' => true, 'targetClass' => Position::class, 'targetAttribute' => ['position_id' => 'id']],

            ['user_id', 'integer'],
            ['user_id', 'required'],
            ['user_id', 'unique', 'message' => 'Этот пользователь уже используется у другого сотрудника'],
            ['user_id', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],

            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            ['status', 'default', 'value'=> self::STATUS_ACTIVE],
            ['status', 'required'],
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
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'full_name' => 'ФИО',
            'birth_date' => 'Дата рождения',
            'user_id' => 'Пользователь',
            'position_id' => 'Должность',
            'position_name' => 'Должность',
            'status' => 'Статус',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * Возвращает связь с должностью сотрудника.
     *
     * @return ActiveQuery Запрос для получения связанной должности
     */
    public function getPosition()
    {
        return $this->hasOne(Position::class, ['id' => 'position_id']);
    }

    /**
     * Возвращает связь с пользователем системы.
     *
     * @return ActiveQuery Запрос для получения связанного пользователя
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Возвращает массив доступных статусов сотрудника.
     *
     * @return string[] Массив статусов в формате [код => название]
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_ACTIVE => 'Действует',
            self::STATUS_INACTIVE => 'Не действует',
        ];
    }

    /**
     * Возвращает текстовое название текущего статуса сотрудника.
     *
     * @return string Название статуса или 'Неизвестный статус' если статус не определен
     * @throws \Exception Если произошла ошибка при получении статуса
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->status, 'Неизвестный статус');
    }

    /**
     * Возвращает массив используемых в системе статусов сотрудников.
     *
     * @return array Массив уникальных статусов
     */
    public static function getAvailableStatuses()
    {
        return self::find()
            ->select('status')
            ->distinct()
            ->where(['status' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]])
            ->column();
    }

    /**
     * Возвращает список активных пользователей системы.
     *
     * @return array Массив пользователей в формате [id => username]
     */
    public static function getActiveUserList()
    {
        return ArrayHelper::map(User::find()->where(['status' => self::STATUS_ACTIVE])->all(), 'id', 'username');
    }

    /**
     * Возвращает список активных должностей, отсортированных по названию.
     *
     * @return array Массив должностей в формате [id => name]
     */
    public static function getActivePositionList()
    {
        return ArrayHelper::map(Position::find()->where(['status' => self::STATUS_ACTIVE])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }

    /**
     * Обработчик перед сохранением модели.
     * Конвертирует дату рождения из формата d.m.Y в Y-m-d для хранения в БД.
     *
     * @param bool $insert Флаг, указывающий выполняется вставка новой записи или обновление
     * @return bool Возвращает true, если операция должна быть продолжена
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!empty($this->birth_date)) {
                $this->birth_date = \DateTime::createFromFormat('d.m.Y', $this->birth_date)->format('Y-m-d');
            }
            return true;
        }
        return false;
    }

    /**
     * Обработчик после выборки данных из БД.
     * Конвертирует дату рождения из формата Y-m-d в d.m.Y для отображения.
     */
    public function afterFind()
    {
        parent::afterFind();
        if (!empty($this->birth_date)) {
            $this->birth_date = \DateTime::createFromFormat('Y-m-d', $this->birth_date)->format('d.m.Y');
        }
    }

    /**
     * Возвращает полное ФИО сотрудника.
     *
     * @return string Полное имя в формате "Фамилия Имя Отчество"
     */
    public function getFullName()
    {
        $nameParts = [$this->last_name, $this->first_name];

        if ($this->middle_name) {
            $nameParts[] = $this->middle_name;
        }

        return implode(' ', $nameParts);
    }

    /**
     * Возвращает название должности сотрудника.
     *
     * @return string Название должности или 'Должность не указана' если должность не задана
     */
    public function getPositionName()
    {
        return $this->position ? $this->position->name : 'Должность не указана';
    }

    /**
     * Возвращает полное ФИО сотрудника с указанием должности в скобках.
     *
     * @return string Строка в формате "Фамилия Имя Отчество (Должность)"
     */
    public function getFullNameAndPosition()
    {
        $nameParts = [$this->last_name, $this->first_name];

        if ($this->middle_name) {
            $nameParts[] = $this->middle_name;
        }

        if ($this->position) {
            $nameParts[] = '(' . $this->position->name . ')';
        }

        return implode(' ', $nameParts);
    }

    /**
     * Возвращает связанного пользователя системы.
     *
     * @return User|null Объект пользователя или null если пользователь не задан
     */
    public function getCurrentUser()
    {
        if ($this->user_id) {
            return User::findOne($this->user_id);
        }
        return null;
    }

    /**
     * Возвращает список текущих пользователей (для выпадающих списков).
     *
     * @return array Массив пользователей в формате [id => username] или пустой массив
     */
    public function getCurrentUserList()
    {
        $currentUser = $this->getCurrentUser();
        return $currentUser ? ArrayHelper::map([$currentUser], 'id', 'username') : [];
    }

    /**
     * Валидатор для проверки уникальности сотрудника по ФИО и дате рождения.
     *
     * @param string $attribute Проверяемый атрибут
     */
    public function validateUniquePerson($attribute)
    {
        if ($this->isDuplicate()) {
            $this->addError($attribute, 'Сотрудник с такими ФИО и датой рождения уже существует.');
        }
    }

    /**
     * Проверяет, существует ли дубликат сотрудника с такими же ФИО и датой рождения.
     *
     * @return bool Возвращает true если дубликат существует
     */
    protected function isDuplicate()
    {
        $date = \DateTime::createFromFormat('d.m.Y', $this->birth_date);
        $query = self::find()->where([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'birth_date' => $date->format('Y-m-d')
        ]);

        if ($this->isNewRecord) {
            return $query->exists();
        } else {
            return $query->andWhere(['!=', 'id', $this->id])->exists();
        }
    }

    /**
     * Возвращает связь с документами РЕМД через промежуточную таблицу.
     *
     * @return ActiveQuery Запрос для получения связанных документов РЕМД
     * @throws InvalidConfigException Если связь настроена неправильно
     */
    public function getRemds()
    {
        return $this->hasMany(Remd::class, ['id' => 'remd_id'])
            ->viaTable('{{%remd_employee}}', ['employee_id' => 'id']);
    }
}
