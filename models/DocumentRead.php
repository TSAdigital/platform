<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "document_read".
 *
 * @property int $id
 * @property int $document_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Document $document
 * @property User $user
 */
class DocumentRead extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_read';
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
            ['document_id', 'required'],
            ['document_id', 'integer'],
            ['document_id', 'exist', 'skipOnError' => true, 'targetClass' => Document::class, 'targetAttribute' => ['document_id' => 'id']],

            ['user_id', 'required'],
            ['user_id', 'integer'],
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
     * @return bool|mixed
     * @throws Exception
     */
    public static function markAsRead($documentId, $userId)
    {
        $documentRead = self::find()->where(['document_id' => $documentId, 'user_id' => $userId])->one();

        if ($documentRead) {
            return self::updateReadTime($documentRead);
        }

        return self::createReadEntry($documentId, $userId);
    }

    /**
     * @param $documentId
     * @param $userId
     * @return bool
     * @throws Exception
     */
    private static function createReadEntry($documentId, $userId)
    {
        $documentRead = new self();
        $documentRead->document_id = $documentId;
        $documentRead->user_id = $userId;

        return $documentRead->save();
    }

    /**
     * @param $documentRead
     * @return mixed
     */
    private static function updateReadTime($documentRead)
    {
        $documentRead->updated_at = time();
        return $documentRead->save();
    }
}
