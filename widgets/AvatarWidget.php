<?php
namespace app\widgets;

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use yii\base\Widget;
use Yii;
use yii\helpers\Html;

class AvatarWidget extends Widget
{
    public $name;
    public $avatarUrl = null;
    public $length = 1;
    public $fontSize = 0.5;
    public $size = 40;
    public $background = '#3c7ddc';
    public $color = '#fff';
    public $imgClass = 'avatar img-fluid rounded me-1';
    public $defaultAvatarClass = 'avatar-default';
    public $userId = null;

    public function init()
    {
        parent::init();

        if ($this->userId === null && !Yii::$app->user->isGuest) {
            $this->userId = Yii::$app->user->id;
        }

        if ($this->avatarUrl) {
            $filePath = $this->getAvatarFilePath($this->avatarUrl);
            if (!$filePath || !file_exists($filePath)) {
                $this->avatarUrl = null;
            }
        }
    }

    public function run()
    {
        $attributes = [
            'class' => $this->imgClass,
            'width' => $this->size,
            'height' => $this->size,
            'alt' => 'User avatar',
            'data-avatar-widget' => '1',
            'data-user-id' => $this->userId,
        ];

        if ($this->avatarUrl) {
            $attributes['src'] = $this->avatarUrl;
            $attributes['class'] .= ' user-uploaded-avatar';
        } else {
            $attributes['class'] .= ' ' . $this->defaultAvatarClass;
            $attributes['src'] = $this->generateDefaultAvatar();
        }

        return Html::tag('img', '', $attributes);
    }

    /**
     * Генерирует аватар по умолчанию (из имени)
     * @return string Base64 encoded image
     */
    protected function generateDefaultAvatar()
    {
        $avatar = (new InitialAvatar)
            ->name($this->name)
            ->length($this->length)
            ->fontSize($this->fontSize)
            ->size($this->size)
            ->background($this->background)
            ->color($this->color)
            ->generate()
            ->stream('png', 100);

        return 'data:image/png;base64,' . base64_encode($avatar->getContents());
    }

    /**
     * Преобразует URL аватара в абсолютный путь к файлу
     * @param string $url Относительный URL аватара
     * @return string|null Абсолютный путь к файлу или null если не удалось преобразовать
     */
    protected function getAvatarFilePath($url)
    {
        $cleanUrl = preg_replace('/\?.*/', '', $url);

        if (strpos($cleanUrl, '/uploads/avatars/') === 0) {
            return Yii::getAlias('@webroot') . $cleanUrl;
        }

        return null;
    }
}