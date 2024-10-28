<?php
namespace app\components;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Yii;
use yii\base\Component;

class S3 extends Component
{
    public $key;
    public $secret;
    public $region;
    public $bucket;
    public $endpoint;
    private $s3Client;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => $this->region,
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ],
            'endpoint' => $this->endpoint,
            //'use_path_style_endpoint' => true,
            'suppress_php_deprecation_warning' => true,
        ]);
    }

    /**
     * @param $filePath
     * @param $key
     * @return false|mixed
     */
    public function uploadFile($filePath, $key)
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ACL' => 'private',
            ]);

            return $result['ObjectURL'];
        } catch (AwsException $e) {
            Yii::error("Ошибка при загрузке файла: " . $e->getMessage());

            return false;
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getFileUrl($key)
    {
        return $this->s3Client->getObjectUrl($this->bucket, $key);
    }

    /**
     * @param $key
     * @param $expiresInSeconds
     * @return false|string
     */
    public function getTemporaryUrl($key, $expires)
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            $request = $this->s3Client->createPresignedRequest($cmd, '+' . $expires . ' seconds');

            return (string) $request->getUri();
        } catch (AwsException $e) {
            Yii::error("Ошибка при создании временного URL-адреса: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function fileExists($key)
    {
        try {
            $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (AwsException $e) {
            if ($e->getStatusCode() === 404) {
                return false;
            }
            Yii::error("Ошибка при проверке существования файла: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param $key
     * @return bool
     */
    public function deleteFile($key)
    {
        if (!$this->fileExists($key)) {
            Yii::warning("Файл не существует: $key");
            return false;
        }

        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (AwsException $e) {
            Yii::error("Ошибка при удалении файла: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param $key
     * @return false|string
     */
    public function getFileChecksum($key)
    {
        try {
            $result = $this->s3Client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return isset($result['ETag']) ? trim($result['ETag'], '"') : false;
        } catch (AwsException $e) {
            Yii::error("Ошибка при получении контрольной суммы файла: " . $e->getMessage());
            return false;
        }
    }
}