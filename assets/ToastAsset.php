<?php

namespace app\assets;

use yii\web\AssetBundle;

class ToastAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/toastify.css',
    ];
    public $js = [
        'js/toastify.js',
    ];
}
