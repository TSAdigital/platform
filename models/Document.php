<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "document".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $createdBy
 * @property DocumentRead $documentRead
 */
class Document extends ActiveRecord
{
    const STATUS_DRAFT = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document';
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
            ['name', 'string', 'max' => 255],
            ['name', 'required'],
            ['name', 'trim'],

            ['description', 'string'],
            ['description', 'trim'],

            ['created_by', 'integer'],
            ['created_by', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],

            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DRAFT]],
            ['status', 'default', 'value'=> self::STATUS_DRAFT],
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
            'name' => 'Наименование',
            'description' => 'Описание',
            'created_by' => 'Автор',
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
     * @return string[]
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_ACTIVE => 'Действует',
            self::STATUS_INACTIVE => 'Отменен',
            self::STATUS_DRAFT => 'Черновик',
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
            ->where(['status' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DRAFT]])
            ->column();
    }

    /**
     * Gets the access records for the document.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessRecords()
    {
        return $this->hasMany(DocumentAccess::class, ['document_id' => 'id']);
    }

    /**
     * Checks if a user has access to the document.
     *
     * @param int $userId The ID of the user.
     * @return bool True if the user has access, false otherwise.
     */
    public function userHasAccess()
    {
        $user_id = Yii::$app->user->id;

        if ($this->created_by == $user_id) {
            return true;
        }

        if ($this->status === self::STATUS_DRAFT) {
            return false;
        }

        return $this->getAccessRecords()
            ->where(['user_id' => $user_id])
            ->exists();
    }

    /**
     * @return ActiveQuery
     */
    public function getDocumentRead()
    {
        return $this->hasMany(DocumentRead::class, ['document_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function isReadByUser()
    {
        $userId = Yii::$app->user->getId();

        if ($this->created_by === $userId) {
            return true;
        }

        return $this->getDocumentRead()->where(['user_id' => $userId])->exists();
    }
}
