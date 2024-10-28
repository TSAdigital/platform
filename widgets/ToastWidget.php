<?php
namespace app\widgets;

use yii\base\Widget;
use Yii;

class ToastWidget extends Widget
{
    public $type;
    public $message;
    public $close = true;

    public function run()
    {
        $toast = Yii::$app->session->getFlash('toast');
        if ($toast) {
            $this->type = $toast['type'] ?? 'info';
            $this->message = $toast['message'] ?? 'Сообщение не указано';
            $this->close = $toast['close'] ?? true;
            $this->registerJs();
        }
    }

    protected function registerJs()
    {
        $options = [
            'text' => $this->message,
            'duration' => 3000,
            'close' => $this->close,
            'gravity' => 'top',
            'position' => 'right',
            'stopOnFocus' => true,
            'style' => [
                'background' => $this->getBackgroundColor(),
            ],
        ];

        $js = 'Toastify(' . json_encode($options) . ').showToast();';
        $this->getView()->registerJs($js);
    }

    private function getBackgroundColor()
    {
        switch ($this->type) {
            case 'success':
                return 'linear-gradient(to right, #3ec59d, #1cbb8c)';
            case 'info':
                return 'linear-gradient(to right, #17a2b8, #148a9c)';
            case 'error':
                return 'linear-gradient(to right, #dc3545, #bb2d3b)';
            case 'warning':
                return 'linear-gradient(to right, #fcc44c, #fcb92c)';
            default:
                return 'linear-gradient(to right, #6c757d, #5c636a)';
        }
    }
}