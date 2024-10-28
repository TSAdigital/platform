<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class TelegramController extends Controller
{
    /**
     * @return void
     */
    public function actionUpdate()
    {
        Yii::$app->telegramBot->getUpdates();
    }
}
