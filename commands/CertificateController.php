<?php

namespace app\commands;

use app\components\SendMessageJob;
use app\models\Certificate;
use Yii;
use yii\console\Controller;

class CertificateController extends Controller
{
    /**
     * @return void
     */
    public function actionCheckValid()
    {
        $today = date('Y-m-d');

        Certificate::updateAll(
            ['status' => Certificate::STATUS_INACTIVE],
            [
                'AND',
                ['status' => Certificate::STATUS_ACTIVE],
                ['<', 'valid_to', $today]
            ]
        );
    }

    /**
     * @return void
     */
    public function actionDeleteInactive()
    {
        Certificate::deleteAll(['status' => Certificate::STATUS_INACTIVE]);
    }

    /**
     * @return void
     */
    public function actionSendMessageTelegramBot()
    {
        $today = date('Y-m-d');
        $warn15Days = date('Y-m-d', strtotime($today . ' + 15 days'));
        $warn30Days = date('Y-m-d', strtotime($today . ' + 30 days'));

        $certificates = Certificate::find()
            ->where(['status' => Certificate::STATUS_ACTIVE])
            ->andWhere(['or',
                ['valid_to' => $warn15Days],
                ['valid_to' => $warn30Days]
            ])
            ->all();

        $telegram_certificate_chat = Yii::$app->params['telegram_certificate_chat'];

        foreach ($certificates as $certificate) {
            $telegram_employee_chat = $certificate->employee->user->telegram_chat_id ?? null;
            $telegramChatIds = array_filter([$telegram_certificate_chat, $telegram_employee_chat]);

            if (!Yii::$app->params['telegram'] || empty($telegramChatIds)) {
                continue;
            }

            $messages = [
                $warn15Days => 'Сертификат электронной подписи на имя ' . $certificate->employee->getFullName() . ' истекает через 15 дней',
                $warn30Days => 'Сертификат электронной подписи на имя ' . $certificate->employee->getFullName() . ' истекает через 30 дней',
            ];

            if (array_key_exists(date('Y-m-d', strtotime($certificate->valid_to)), $messages)) {
                foreach ($telegramChatIds as $telegramChatId) {
                    if ($telegramChatId) {
                        Yii::$app->queue->push(new SendMessageJob([
                            'chatId' => $telegramChatId,
                            'messageText' => $messages[date('Y-m-d', strtotime($certificate->valid_to))],
                        ]));
                    }
                }
            }
        }
    }
}
