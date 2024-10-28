<?php
namespace app\components;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\web\NotFoundHttpException;

class SendMessageJob extends BaseObject implements JobInterface
{
    public $chatId;
    public $messageText;
    public $mode;

    /**
     * @param $queue
     * @return void
     * @throws NotFoundHttpException
     */
    public function execute($queue)
    {
        if(!Yii::$app->telegramBot->sendMessage($this->chatId, $this->messageText, $this->mode)) {
            throw new NotFoundHttpException();
        }
    }
}