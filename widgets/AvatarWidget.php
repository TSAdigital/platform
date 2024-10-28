<?php
namespace app\widgets;

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use yii\base\Widget;

class AvatarWidget extends Widget
{
    public $name;
    public $length = 1;
    public $fontSize = 0.5;
    public $size = 40;
    public $background = '#3c7ddc';
    public $color = '#fff';
    public $imgClass = 'avatar img-fluid rounded me-1';

    public function run()
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

        $base64 = base64_encode($avatar->getContents());

        return "<img src='data:image/png;base64,{$base64}' class='{$this->imgClass}' alt='avatar' />";
    }
}