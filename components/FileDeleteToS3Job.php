<?php
namespace app\components;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\web\NotFoundHttpException;

class FileDeleteToS3Job extends BaseObject implements JobInterface
{
    public $s3Key;

    /**
     * @param $queue
     * @return void
     * @throws NotFoundHttpException
     */
    public function execute($queue)
    {
        $s3 = Yii::$app->s3;

        if ($s3->fileExists($this->s3Key)) {
            if(!$s3->deleteFile($this->s3Key)) {
                throw new NotFoundHttpException();
            }
        }
    }
}
