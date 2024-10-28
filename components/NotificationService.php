<?php
namespace app\components;

use Yii;
use yii\base\Component;

class NotificationService extends Component
{
    /**
     * @param $message
     * @param $type
     * @param $close
     * @return void
     */
    public function send($message, $type = 'success', $close = true)
    {
        Yii::$app->session->setFlash('toast', [
            'type' => $type,
            'message' => $message,
            'close' => $close,
        ]);
    }

    /**
     * @param $message
     * @param $close
     * @return void
     */
    public function success($message, $close = true)
    {
        $this->send($message, 'success', $close);
    }

    /**
     * @param $message
     * @param $close
     * @return void
     */
    public function error($message, $close = true)
    {
        $this->send($message, 'error', $close);
    }

    /**
     * @param $message
     * @param $close
     * @return void
     */
    public function info($message, $close = true)
    {
        $this->send($message, 'info', $close);
    }

    /**
     * @param $message
     * @param $close
     * @return void
     */
    public function warning($message, $close = true)
    {
        $this->send($message, 'warning', $close);
    }
}
