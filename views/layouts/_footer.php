<?php

use yii\bootstrap5\Html;
?>

<footer class="footer">
    <div class="container-fluid">
        <div class="row text-muted">
            <div class="col-8 text-start">
                <p class="mb-0">
                    Разработано в <?= Html::a('TSAdigital', 'https://tsa-digital.ru', ['target' => '_blank']) ?>
                    на базе <?= Html::a('Yii2', 'https://www.yiiframework.com', ['target' => '_blank']) ?> и <?= Html::a('AdminKit', 'https://adminkit.io', ['target' => '_blank']) ?>
                </p>
            </div>
            <div class="col-4 text-end">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        Версия 1.0.1
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>