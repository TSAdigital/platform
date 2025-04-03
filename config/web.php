<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'language' => 'ru',
    'timeZone' => 'Asia/Yekaterinburg',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'container' => [
        'definitions' => [
            \yii\widgets\LinkPager::class => \yii\bootstrap5\LinkPager::class,
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'mUAy4mE2YbBr_rX7V-RBj7iDK4b0zosC',
        ],
        'qrCodeGenerator' => [
            'class' => 'app\components\QrCodeGenerator',
        ],
        'telegramBot' => [
            'class' => 'app\components\TelegramBot',
            'apiToken' => '<ваш токен>',
        ],
        's3' => [
            'class' => 'app\components\S3',
            'key' => '<ваш ключ>',
            'secret' => '<ваш секретный ключ>',
            'region' => '<регион>',
            'bucket' => '<бакет>',
            'endpoint' => '<адрес сервера>',
        ],
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'strictJobType' => false,
            'serializer' => \yii\queue\serializers\JsonSerializer::class,
            'ttr' => 600,
            'attempts' => 120,
        ],
        'mutex'=> [
            'class' => 'yii\mutex\MysqlMutex'
        ],
        'session' => [
            'class' => 'yii\web\DbSession',
        ],
        'formatter' => [
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y, H:i',
            'timeFormat' => 'php:H:i',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'notification' => [
            'class' => 'app\components\NotificationService',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'help' => 'site/help',
                'about' => 'site/about',
                'profile' => 'site/profile',

                'documents' => 'document/index',
                'document/create' => 'document/create',
                'document/<id:\d+>' => 'document/view',
                'document/<id:\d+>/update' => 'document/update',
                'document/<id:\d+>/publish' => 'document/publish',
                'document/<id:\d+>/cancel' => 'document/cancel',
                'document/<id:\d+>/<action:(file-delete|upload|download|add-access|cancel-access)>' => 'document/<action>',

                'employees' => 'employee/index',
                'employee/create' => 'employee/create',
                'employee/<id:\d+>' => 'employee/view',
                'employee/<id:\d+>/update' => 'employee/update',

                'positions' => 'position/index',
                'position/create' => 'position/create',
                'position/<id:\d+>' => 'position/view',
                'position/<id:\d+>/update' => 'position/update',

                'issuers' => 'issuer/index',
                'issuer/create' => 'issuer/create',
                'issuer/<id:\d+>' => 'issuer/view',
                'issuer/<id:\d+>/update' => 'issuer/update',

                'certificates' => 'certificate/index',
                'certificate/create' => 'certificate/create',
                'certificates/analytics' => 'certificate/analytics',
                'certificate/<id:\d+>' => 'certificate/view',
                'certificate/<id:\d+>/update' => 'certificate/update',

                'users' => 'user/index',
                'user/create' => 'user/create',
                'user/<id:\d+>' => 'user/view',
                'user/<id:\d+>/update' => 'user/update',
                'user/<id:\d+>/block' => 'user/block',
                'user/<id:\d+>/unlock' => 'user/unlock',
                'user/<id:\d+>/password' => 'user/change-password',

                'roles' => 'auth-item/index',
                'roles/create' => 'auth-item/create',
                'roles/<name:\w+>' => 'auth-item/view',
                'roles/<name:\w+>/update' => 'auth-item/update',
                'roles/<name:\w+>/block' => 'auth-item/block',
                'roles/<name:\w+>/unlock' => 'auth-item/unlock',
                'roles/<name:\w+>/permission' => 'auth-item/permission-update',
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        //'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        //'allowedIPs' => ['*'],
    ];
}

return $config;
