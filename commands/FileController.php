<?php

namespace app\commands;

use app\models\DocumentFile;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

class FileController extends Controller
{
    /**
     * @return void
     */
    public function actionUploadFileToS3()
    {
        $files = DocumentFile::find()->where(['s3_storage' => 0])->limit(10)->all();
        $s3 = Yii::$app->s3;

        foreach ($files as $file) {
            $filePath = Yii::getAlias('@app/web/uploads/' . $file->stored_name);
            $s3Key = $file->stored_name;

            if(!$s3->fileExists($s3Key)) {
                $result = $s3->uploadFile($filePath, $s3Key);

                if ($result) {
                    echo "Файл загружен: " . $file->name . "\n";
                } else {
                    echo "Ошибка при загрузке файла: " . $file->name . "\n";
                }
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionExistsFileToS3()
    {
        $files = DocumentFile::find()->where(['s3_storage' => 0])->limit(10)->all();
        $s3 = Yii::$app->s3;

        foreach ($files as $file) {
            $localFilePath = Yii::getAlias('@app/web/uploads/' . $file->stored_name);
            $s3Key = $file->stored_name;

            $localFileChecksum = md5_file($localFilePath);
            $s3Checksum = $s3->getFileChecksum($s3Key);

            if($s3Checksum === $localFileChecksum && $s3->fileExists($s3Key)) {
                $file->s3_storage = 1;
                $file->save(false);
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function actionDeleteFileToLocalStorage()
    {
        $files = DocumentFile::find()->where(['s3_storage' => 1, 'local_storage' => 1])->limit(10)->all();
        $s3 = Yii::$app->s3;

        foreach ($files as $file) {
            $localFilePath = Yii::getAlias('@app/web/uploads/' . $file->stored_name);
            $s3Key = $file->stored_name;

            if(is_file($localFilePath) && $s3->fileExists($s3Key)) {
                $localFileChecksum = md5_file($localFilePath);
                $s3Checksum = $s3->getFileChecksum($s3Key);

                if($s3Checksum === $localFileChecksum) {
                    if(unlink($localFilePath)){
                        $file->local_storage = 0;
                        $file->save(false);

                        echo "Файл удален: " . $file->name . "\n";
                    } else {
                        echo "Не удалось удалить файл: " . $file->name . "\n";
                    }
                }
            }
        }
    }
}
