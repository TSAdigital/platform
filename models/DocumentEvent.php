<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "document_event".
 *
 * @property int $id
 * @property int $document_id
 * @property string $event
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Document $document
 * @property User $user
 */
class DocumentEvent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_event';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['event', 'string'],
            ['event', 'trim'],
            ['event', 'required'],

            ['document_id', 'integer'],
            ['document_id', 'required'],
            ['document_id', 'exist', 'skipOnError' => true, 'targetClass' => Document::class, 'targetAttribute' => ['document_id' => 'id']],

            ['user_id', 'integer'],
            ['user_id', 'required'],
            ['user_id', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'event' => 'Событие',
            'document_id' => 'Документ',
            'user_id' => 'Пользователь',
            'created_at' => 'Дата просмотра',
            'updated_at' => 'Дата последнего просмотра',
        ];
    }

    /**
     * Gets query for [[Document]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
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
     * @param $documentId
     * @param $userId
     * @param $event
     * @return bool
     * @throws Exception
     */
    public static function createEvent($documentId, $userId, $event)
    {
        if (self::eventExists($documentId, $userId, $event)) {
            return false;
        }

        return self::saveEvent($documentId, $userId, $event);
    }

    /**
     * @param $documentId
     * @param $userId
     * @param $event
     * @return bool
     */
    private static function eventExists($documentId, $userId, $event)
    {
        return self::find()
            ->where(['document_id' => $documentId, 'user_id' => $userId, 'event' => $event])
            ->exists();
    }

    /**
     * @param $documentId
     * @param $userId
     * @param $event
     * @return bool
     * @throws Exception
     */
    private static function saveEvent($documentId, $userId, $event)
    {
        $documentEvent = new self();
        $documentEvent->document_id = $documentId;
        $documentEvent->user_id = $userId;
        $documentEvent->event = $event;

        return $documentEvent->save();
    }
}
