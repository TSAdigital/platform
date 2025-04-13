<?php

namespace app\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "certificate".
 *
 * @property int $id
 * @property int $employee_id
 * @property string|null $serial_number
 * @property string $valid_from
 * @property string $valid_to
 * @property int $issuer_id
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $createdBy
 * @property Employee $employee
 * @property Issuer $issuer
 * @property User $updatedBy
 */
class Certificate extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'certificate';
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
            ['valid_from', 'date', 'format' => 'php:d.m.Y'],
            ['valid_from', 'trim'],
            ['valid_from', 'required'],

            ['valid_to', 'date', 'format' => 'php:d.m.Y'],
            ['valid_to', 'trim'],
            ['valid_to', 'required'],

            ['serial_number', 'string', 'max' => 255],
            ['serial_number', 'trim'],
            ['serial_number', 'default', 'value' => null],
            ['serial_number', 'unique', 'skipOnEmpty' => true],

            ['employee_id', 'integer'],
            ['employee_id', 'required'],
            ['employee_id', 'exist', 'skipOnError' => true, 'targetClass' => Employee::class, 'targetAttribute' => ['employee_id' => 'id']],

            ['issuer_id', 'integer'],
            ['issuer_id', 'required'],
            ['issuer_id', 'exist', 'skipOnError' => true, 'targetClass' => Issuer::class, 'targetAttribute' => ['issuer_id' => 'id']],

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
            'employee_id' => 'Субъект',
            'serial_number' => 'Серийный номер',
            'valid_from' => 'Действует с',
            'valid_to' => 'Действует по',
            'issuer_id' => 'Издатель',
            'status' => 'Статус',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }

    /**
     * Gets query for [[Issuer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIssuer()
    {
        return $this->hasOne(Issuer::class, ['id' => 'issuer_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @param $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!empty($this->valid_from) && !empty($this->valid_to)) {
                $this->valid_from = \DateTime::createFromFormat('d.m.Y', $this->valid_from)->format('Y-m-d');
                $this->valid_to = \DateTime::createFromFormat('d.m.Y', $this->valid_to)->format('Y-m-d');
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
        if (!empty($this->valid_from) && !empty($this->valid_to)) {
            $this->valid_from = \DateTime::createFromFormat('Y-m-d', $this->valid_from)->format('d.m.Y');
            $this->valid_to = \DateTime::createFromFormat('Y-m-d', $this->valid_to)->format('d.m.Y');
        }
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
    public static function getActiveIssuerList()
    {
        return ArrayHelper::map(Issuer::find()->where(['status' => self::STATUS_ACTIVE])->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }

    /**
     * @return Employee|null
     */
    public function getCurrentEmployee()
    {
        if ($this->employee_id) {
            return Employee::findOne($this->employee_id);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getCurrentEmployeeList()
    {
        $currentEmployee = $this->getCurrentEmployee();
        return $currentEmployee ? ArrayHelper::map([$currentEmployee], 'id', 'fullName') : [];
    }
}
