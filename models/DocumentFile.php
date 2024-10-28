<?php

namespace app\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "document_file".
 *
 * @property int $id
 * @property int $document_id
 * @property string $name
 * @property string $stored_name
 * @property int $size
 * @property string $type
 * @property int $s3_storage
 * @property int $local_storage
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $createdBy
 */
class DocumentFile extends ActiveRecord
{
    public $file;
    public $fileSize = 50; //Размер файла в мегабайтах
    public $maxFiles = 10; //Максимальное количество загружаемых файлов

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_file';
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

            ['size', 'integer'],
            ['size', 'required'],

            ['stored_name', 'string', 'max' => 255],
            ['stored_name', 'required'],

            ['type', 'string', 'max' => 50],
            ['type', 'required'],

            ['s3_storage', 'integer'],

            ['local_storage', 'integer'],

            ['document_id', 'integer'],
            ['document_id', 'required'],
            ['document_id', 'exist', 'skipOnError' => true, 'targetClass' => Document::class, 'targetAttribute' => ['document_id' => 'id']],

            ['created_by', 'integer'],
            ['created_by', 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],

            [['file'], 'file',
                'skipOnEmpty' => false,
                'extensions' => 'jpg, jpeg, png, pdf, zip, 7z',
                'maxSize' => $this->fileSize * 1024 * 1024,
                'maxFiles' => $this->maxFiles,
                'tooBig' => 'Размер файла не должен превышать' . $this->fileSize . 'МБ.',
            ],
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
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
