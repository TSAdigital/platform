<?php

namespace app\controllers;

use app\components\QrCodeGenerator;
use app\models\Certificate;
use app\models\Document;
use app\models\DocumentEvent;
use app\models\Remd;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'index', 'about', 'profile', 'help'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'profile', 'help'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['about'],
                        'allow' => true,
                        'roles' => ['administrator'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'layout' => 'error',
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @return Response
     */
    public function actionIndex()
    {
        return $this->redirect(['site/profile']);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Displays help page.
     *
     * @return string
     */
    public function actionHelp()
    {
        $qrCodeGenerator = new QrCodeGenerator();
        $telegramSupport= Yii::$app->params['telegram_support'];

        return $this->render('help', [
                'qrCodeGenerator' => $qrCodeGenerator,
                'telegramSupport' => $telegramSupport,
            ]
        );
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function actionProfile()
    {
        $qrCodeGenerator = new QrCodeGenerator();

        $telegramBotUrl = Yii::$app->params['telegram_bot'] . '?start=' . Yii::$app->user->identity->unique_id;

        $userId = Yii::$app->user->identity->id;

        $documentIds = array_column(Document::findAll(['created_by' => $userId]), 'id');

        $documentEvents = DocumentEvent::find()
            ->where(['document_id' => $documentIds])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(30)
            ->all();

        $eventDataProvider = new ArrayDataProvider([
            'allModels' => $documentEvents,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                ],
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $employeeId = isset(Yii::$app->user->identity->employee->id) ? Yii::$app->user->identity->employee->id : null;

        $certificates = Certificate::find()->where(['employee_id' => $employeeId, 'status' => Certificate::STATUS_ACTIVE])->all();

        $groupedDocuments = $employeeId ? Remd::getGroupedByEmployee($employeeId) : null;

        return $this->render('profile', [
            'qrCodeGenerator' => $qrCodeGenerator,
            'telegramBotUrl' => $telegramBotUrl,
            'eventDataProvider' => $eventDataProvider,
            'certificates' => $certificates,
            'groupedDocuments' => $groupedDocuments,
        ]);
    }
}
