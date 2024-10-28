<?php

namespace app\controllers;

use app\models\Employee;
use app\models\EmployeeSearch;
use app\models\User;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * EmployeeController implements the CRUD actions for Employee model.
 */
class EmployeeController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index'],
                            'roles' => ['viewEmployeeList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['view'],
                            'roles' => ['viewEmployee'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['create'],
                            'roles' => ['createEmployee'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['update'],
                            'roles' => ['updateEmployee'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['user-list'],
                            'roles' => ['updateEmployee'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Employee models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmployeeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Employee model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Employee model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Employee();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($model->save()) {
                    Yii::$app->notification->success('Сотрудник добавлен');

                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->notification->error('Не удалось добавить сотрудника');
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
     * Updates an existing Employee model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                Yii::$app->notification->success('Сотрудник изменен');

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->notification->error('Не удалось изменить сотрудника');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Employee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Employee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Employee::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * @param string|null $q
     * @param int|null $id
     * @return array|array[]
     */
    public function actionUserList(string $q = null, int $id = null) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => []];

        $query = new Query;

        $query->select('id, username AS text')
            ->from('user');

        if (is_string($q)) {
            $q = trim($q);

            if (strlen($q) > 255) {
                $q = substr($q, 0, 255);
            }

            $query->where(['like', 'username', $q])
                ->andWhere(['status' => User::STATUS_ACTIVE])
                ->limit(20);

        } elseif (is_int($id) && $id > 0) {
            $user = User::find()->where(['id' => $id])->one();

            if ($user) {
                $out['results'][] = ['id' => $id, 'text' => $user->username];
            }
        }

        $data = $query->orderBy(['username' => SORT_DESC])->all();

        $out['results'] = array_merge($out['results'], $data);

        return $out;
    }
}
