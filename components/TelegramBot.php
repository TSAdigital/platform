<?php

/*
 * Update and send
 * Yii::$app->telegramBot->getUpdates();
 * Yii::$app->telegramBot->sendMessage('chat_id', 'message');
 *
 * queue send message
 * Yii::$app->queue->push(new SendMessageJob([
 *   'chatId' => 'chat-id',
 *   'messageText' => 'message',
 * ]));
 */

namespace app\components;

use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\db\DataReader;
use yii\db\Exception;

class TelegramBot extends BaseObject
{
    public $apiToken;

    /**
     * @return void
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        if ($this->apiToken === null) {
            throw new \Exception('API Token is required.');
        }
    }

    /**
     * @param $method
     * @param $data
     * @return mixed
     */
    private function apiRequest($method, $data = [])
    {
        $url = "https://api.telegram.org/bot{$this->apiToken}/{$method}";
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);

        return json_decode(file_get_contents($url, false, $context), true);
    }

    /**
     * @param $chatId
     * @param $text
     * @param null $mode
     * @return mixed
     */
    public function sendMessage($chatId, $text, $mode = null)
    {
        return $this->apiRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $mode
        ]);
    }

    /**
     * @param $limit
     * @param $timeout
     * @return mixed
     * @throws Exception
     */
    public function getUpdates($limit = 100, $timeout = 0)
    {
        $offset = $this->getOffset();

        $response = $this->apiRequest('getUpdates', [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => $timeout,
        ]);

        if (isset($response['result'])) {
            foreach ($response['result'] as $update) {
                $this->processUpdate($update);
                $this->updateOffset($update['update_id'] + 1);
            }
        }

        return $response;
    }

    /**
     * @return false|int|string|DataReader
     * @throws Exception
     */
    private function getOffset()
    {
        $offsetRecord = Yii::$app->db->createCommand('SELECT offset FROM telegram_offsets WHERE id = 1')->queryScalar();

        return $offsetRecord ?: 0;
    }

    /**
     * @param $newOffset
     * @return void
     * @throws Exception
     */
    private function updateOffset($newOffset)
    {
        Yii::$app->db->createCommand()
            ->update('telegram_offsets', ['offset' => $newOffset], 'id = 1')
            ->execute();
    }

    /**
     * @param $update
     * @return void
     * @throws Exception
     */
    private function processUpdate($update)
    {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $messageText = isset($update['message']['text']) ? $update['message']['text'] : '';

            if (strpos($messageText, '/start') === 0) {
                $parts = explode(' ', $messageText);

                if (count($parts) > 1) {
                    $key = $parts[1];
                    $user = User::findOne(['unique_id' => $key]);

                    if ($user && $user->telegram_chat_id == null) {
                        $user->telegram_chat_id = $chatId;
                        if ($user->save(false)) {
                            Yii::$app->queue->push(new SendMessageJob([
                               'chatId' => $chatId,
                               'messageText' => 'Вы успешно подключились к нашему Телеграм-боту.',
                            ]));
                        }
                    }
                }
            }
        }
    }
}
