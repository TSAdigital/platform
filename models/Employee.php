<?php

namespace app\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "employee".
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property string $full_name
 * @property string $birth_date
 * @property int $user_id
 * @property int $position_id
 * @property string $position_name
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Position $position
 * @property User $user
 */
class Employee extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * Gets query for [[Position]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosition()
    {
        return $this->hasOne(Position::class, ['id' => 'position_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return string[]
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_ACTIVE => 'Действует',
            self::STATUS_INACTIVE => 'Не действует',
        ];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->status, 'Неизвестный статус');
    }

    /**
     * @return array
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
     * @return array
     */
    public static function getActiveUserList()
    {
        return ArrayHelper::map(User::find()->where(['status' => self::STATUS_ACTIVE])->all(), 'id', 'username');
    }

    /**
     * @return array
     */
    public static function getActivePositionList()
    {
        return ArrayHelper::map(Position::find()->where(['status' => self::STATUS_ACTIVE])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }

    /**
     * @param $insert
     * @return bool
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
     * @return void
     */
    public function afterFind()
    {
        parent::afterFind();
        if (!empty($this->birth_date)) {
            $this->birth_date = \DateTime::createFromFormat('Y-m-d', $this->birth_date)->format('d.m.Y');
        }
    }

    /**
     * @return string
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
     * @return string
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
     * @return User|null
     */
    public function getCurrentUser()
    {
        if ($this->user_id) {
            return User::findOne($this->user_id);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getCurrentUserList()
    {
        $currentUser = $this->getCurrentUser();
        return $currentUser ? ArrayHelper::map([$currentUser], 'id', 'username') : [];
    }

    /**
     * @param $attribute
     * @return void
     */
    public function validateUniquePerson($attribute)
    {
        if ($this->isDuplicate()) {
            $this->addError($attribute, 'Сотрудник с такими ФИО и датой рождения уже существует.');
        }
    }

    /**
     * @return bool
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
}
