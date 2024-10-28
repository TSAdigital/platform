<?php

namespace app\controllers;

use app\models\User;
use app\models\UserSearch;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'block' => ['POST'],
                        'unlock' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index'],
                            'roles' => ['viewUserList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['view'],
                            'roles' => ['viewUser'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['create'],
                            'roles' => ['createUser'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['update'],
                            'roles' => ['updateUser'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['block'],
                            'roles' => ['blockUser'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['unlock'],
                            'roles' => ['unlockUser'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['change-password'],
                            'roles' => ['changePasswordUser'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|Response
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new User();

        $model->scenario = User::SCENARIO_PASSWORD;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->generateAuthKey();
                $model->setPassword($model->password);
                $model->unique_id = Yii::$app->security->generateRandomString(12);
                if ($model->save()) {
                    Yii::$app->notification->success('Пользователь добавлен');

                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->notification->error('Не удалось добавить пользователя');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                Yii::$app->notification->success('Пользователь изменен');

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->notification->error('Не удалось изменить пользователя');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionBlock($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== $model::STATUS_ACTIVE) {
            Yii::$app->notification->error('Пользователь уже заблокирован');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = $model::STATUS_INACTIVE;

        if ($model->setRole() && $model->save()) {
            Yii::$app->notification->success('Пользователь заблокирован');
        } else {
            Yii::$app->notification->error('Не удалось заблокировать пользователя');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }


    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionUnlock($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== $model::STATUS_INACTIVE) {
            Yii::$app->notification->error('Пользователь уже разблокирован');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = $model::STATUS_ACTIVE;

        if ($model->setRole() && $model->save()) {
            Yii::$app->notification->success('Пользователь разблокирован');
        } else {
            Yii::$app->notification->error('Не удалось разблокировать пользователя');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    public function actionChangePassword($id)
    {
        $model = $this->findModel($id);
        $model->scenario = User::SCENARIO_PASSWORD;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->setPassword($model->password);

            if ($model->setRole() && $model->save()) {
                Yii::$app->notification->success('Пароль изменен');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->notification->error('Не удалось изменить пароль');
            }
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }
}
