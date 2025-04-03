<?php

namespace app\controllers;

use app\models\Certificate;
use app\models\CertificateSearch;
use app\models\Employee;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CertificateController implements the CRUD actions for Certificate model.
 */
class CertificateController extends Controller
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
                            'roles' => ['viewCertificateList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['view'],
                            'roles' => ['viewCertificate'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['analytics'],
                            'roles' => ['viewCertificateAnalytics'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['create', 'employee-list'],
                            'roles' => ['createCertificate'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['update', 'employee-list'],
                            'roles' => ['updateCertificate'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Certificate models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CertificateSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Certificate model.
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
     * Creates a new Certificate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Certificate();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Certificate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Certificate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Certificate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Certificate::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * @param string|null $q
     * @param int|null $id
     * @return array|array[]
     */
    public function actionEmployeeList(string $q = null, int $id = null) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => []];

        $query = new Query;

        $query->select(['id', 'CONCAT_WS(Char(32), last_name, first_name, middle_name) AS text'])
            ->from('employee');

        if (is_string($q)) {
            $q = trim($q);

            if (strlen($q) > 255) {
                $q = substr($q, 0, 255);
            }

            $query->where(['like', 'CONCAT_WS(Char(32), last_name, first_name, middle_name)', $q])
                ->andWhere(['status' => Employee::STATUS_ACTIVE])
                ->limit(20);

        } elseif (is_int($id) && $id > 0) {
            $employee = Employee::find()->where(['id' => $id])->one();

            if ($employee) {
                $out['results'][] = ['id' => $id, 'text' => $employee->getFullName()];
            }
        }

        $data = $query->orderBy(['last_name' => SORT_DESC])->all();

        $out['results'] = array_merge($out['results'], $data);

        return $out;
    }

    /**
     * @return string
     */
    public function actionAnalytics()
    {
        $certificatesDataProvider = new ArrayDataProvider([
            'allModels' => Certificate::find()->all(),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'status',
                    'valid_to',
                    'id',
                ],
                'defaultOrder' => [
                    'status' => SORT_DESC,
                    'valid_to' => SORT_ASC,
                    'id' => SORT_ASC,
                ],
            ],
        ]);

        $data = Certificate::find()
            ->select([
                'COUNT(*) as total_count',
                'SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_count',
                'SUM(CASE WHEN status != 1 THEN 1 ELSE 0 END) as inactive_count'
            ])
            ->asArray()
            ->one();

        $totalCertificates = $data['total_count'];
        $activeCertificates = $data['active_count'];
        $inactiveCertificates = $data['inactive_count'];

        return $this->render('analytics', [
            'certificatesDataProvider' => $certificatesDataProvider,
            'totalCertificates' => $totalCertificates,
            'activeCertificates' => $activeCertificates,
            'inactiveCertificates' => $inactiveCertificates,
        ]);
    }
}
