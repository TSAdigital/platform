<?php

namespace app\controllers;

use app\models\Employee;
use app\models\Position;
use app\models\Remd;
use app\models\RemdBaseSetting;
use app\models\RemdEmployee;
use app\models\RemdPlan;
use app\models\RemdTypeSetting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RemdController extends Controller
{
    /**
     * Определяет поведения и правила доступа для контроллера.
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
                            'actions' => ['index', 'employee-list', 'position-list', 'analytics', 'employee-documents'],
                            'roles' => ['viewRemdList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['base-setting', 'type-setting', 'plan', 'create-plan',  'update-plan', 'delete-plan', 'flush-cache'],
                            'roles' => ['makeRemdSetting'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Основное действие для отображения списка документов РЕМД.
     * Позволяет фильтровать документы по сотрудникам, должностям, датам и типам.
     *
     * @return string Результат рендеринга представления
     * @throws Exception Если произошла ошибка при получении настроек
     */
    public function actionIndex()
    {
        $baseSettings = RemdBaseSetting::getSettings();
        $typeSettings = RemdTypeSetting::getSettings();

        $searchParams = Yii::$app->request->get();
        $currentYear = date('Y');
        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';
        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $documentType = $searchParams['document_type'] ?? '';
        $employeeId = $searchParams['employee_id'] ?? null;
        $positionId = $searchParams['position_id'] ?? null;
        $allDocuments = $searchParams['all_documents'] ?? null;
        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        $page = $searchParams['page'] ?? 1;
        $pageSize = $searchParams['per-page'] ?? $baseSettings->page_size ?? 10;

        $cacheKey = [
            'remd_index',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'documentType' => $documentType,
            'employeeId' => $employeeId,
            'positionId' => $positionId,
            'allDocuments' => $allDocuments,
            'enabledDocTypes' => $enabledDocTypes,
            'pageSize' => $pageSize,
            'page' => $page,
        ];

        $cache = Yii::$app->cache;
        $cachedData = $cache->get($cacheKey);

        if ($cachedData === false) {
            $query = Employee::find()
                ->select([
                    'employee.id',
                    'employee.first_name',
                    'employee.last_name',
                    'employee.middle_name',
                    'employee.position_id',
                    'total_documents' => 'COUNT(remd_employee.remd_id)',
                ])
                ->with('position')
                ->innerJoin('remd_employee', 'remd_employee.employee_id = employee.id')
                ->innerJoin(['remd_alias' => 'remd'], 'remd_alias.id = remd_employee.remd_id');

            if ($dateFrom && $dateTo) {
                $query->andWhere(['between', 'remd_alias.registration_date', $dateFrom, $dateTo]);
            }

            if ($documentType) {
                $query->andWhere(['remd_alias.type' => $documentType]);
            }

            if ($employeeId) {
                $query->andWhere(['employee.id' => $employeeId]);
            }

            if ($positionId) {
                $query->andWhere(['employee.position_id' => $positionId]);
            }

            if ($enabledDocTypes and !$allDocuments) {
                $query->andWhere(['remd_alias.type' => $enabledDocTypes]);
            }

            $dataProvider = new ActiveDataProvider([
                'query' => $query->groupBy('employee.id'),
                'pagination' => [
                    'pageSize' => $baseSettings->page_size ? $baseSettings->page_size : 10,
                ],
                'sort' => [
                    'attributes' => [
                        'fullName' => [
                            'asc' => ['employee.last_name' => SORT_ASC, 'employee.first_name' => SORT_ASC, 'employee.middle_name' => SORT_ASC],
                            'desc' => ['employee.last_name' => SORT_DESC, 'employee.first_name' => SORT_DESC, 'employee.middle_name' => SORT_DESC],
                            'label' => 'ФИО',
                            'default' => SORT_ASC,
                        ],
                    ],
                    'defaultOrder' => [
                        'fullName' => SORT_ASC,
                    ],
                ],
            ]);

            $models = $dataProvider->getModels();

            $employeeIds = array_map(fn($model) => $model->id, $models);

            $documentTypesQuery = Remd::find()
                ->select([
                    'remd_employee.employee_id',
                    'remd.type',
                    'count' => 'COUNT(*)',
                ])
                ->innerJoin('remd_employee', 'remd_employee.remd_id = remd.id')
                ->where(['remd_employee.employee_id' => $employeeIds]);

            if ($dateFrom && $dateTo) {
                $documentTypesQuery->andWhere(['between', 'remd.registration_date', $dateFrom, $dateTo]);
            }

            if ($documentType) {
                $documentTypesQuery->andWhere(['remd.type' => $documentType]);
            }

            if ($enabledDocTypes and !$allDocuments) {
                $documentTypesQuery->andWhere(['remd.type' => $enabledDocTypes]);
            }

            $documentTypesData = $documentTypesQuery
                ->groupBy(['remd_employee.employee_id', 'remd.type'])
                ->orderBy(['remd.type' => SORT_ASC])
                ->asArray()
                ->all();

            $documentTypesByEmployee = [];
            foreach ($documentTypesData as $item) {
                $documentTypesByEmployee[$item['employee_id']][] = [
                    'type' => $item['type'],
                    'count' => $item['count'],
                ];
            }

            $result = [];
            foreach ($models as $employee) {
                $result[] = [
                    'id' => $employee->id,
                    'full_name' => $employee->last_name . ' ' . $employee->first_name . ' ' . $employee->middle_name,
                    'position' => $employee->getPositionName(),
                    'total_documents' => $employee->total_documents,
                    'document_types' => $documentTypesByEmployee[$employee->id] ?? [],
                ];
            }

            $totalDocumentsQuery = Remd::find()
                ->innerJoin('remd_employee', 'remd_employee.remd_id = remd.id')
                ->innerJoin('employee', 'employee.id = remd_employee.employee_id');

            if ($dateFrom && $dateTo) {
                $totalDocumentsQuery->andWhere(['between', 'remd.registration_date', $dateFrom, $dateTo]);
            }

            if ($documentType) {
                $totalDocumentsQuery->andWhere(['remd.type' => $documentType]);
            }

            if ($positionId) {
                $totalDocumentsQuery->andWhere(['employee.position_id' => $positionId]);
            }

            if ($enabledDocTypes and !$allDocuments) {
                $totalDocumentsQuery->andWhere(['remd.type' => $enabledDocTypes]);
            }

            if ($employeeId) {
                $totalDocumentsQuery->andWhere(['remd_employee.employee_id' => $employeeId]); // Фильтрация по сотруднику
            }

            $totalDocumentsQuery->select('remd.id')->groupBy('remd.id');
            $totalDocuments = $totalDocumentsQuery->count();

            $uniqueDocumentTypesQuery = Remd::find()
                ->select(['type' => 'remd.type', 'count' => 'COUNT(DISTINCT remd.id)'])
                ->innerJoin('remd_employee', 'remd_employee.remd_id = remd.id')
                ->innerJoin('employee', 'employee.id = remd_employee.employee_id');

            if ($dateFrom && $dateTo) {
                $uniqueDocumentTypesQuery->andWhere(['between', 'remd.registration_date', $dateFrom, $dateTo]);
            }

            if ($documentType) {
                $uniqueDocumentTypesQuery->andWhere(['remd.type' => $documentType]);
            }

            if ($positionId) {
                $uniqueDocumentTypesQuery->andWhere(['employee.position_id' => $positionId]);
            }

            if ($enabledDocTypes and !$allDocuments) {
                $uniqueDocumentTypesQuery->andWhere(['remd.type' => $enabledDocTypes]);
            }

            if ($employeeId) {
                $uniqueDocumentTypesQuery->andWhere(['remd_employee.employee_id' => $employeeId]); // Фильтрация по сотруднику
            }

            $uniqueDocumentTypes = $uniqueDocumentTypesQuery
                ->groupBy('remd.type')
                ->orderBy(['remd.type' => SORT_ASC])
                ->asArray()
                ->all();

            $uniqueEmployeesWithDocumentsQuery = RemdEmployee::find()
                ->select('employee.id')
                ->distinct()
                ->innerJoin(['remd_alias' => 'remd'], 'remd_alias.id = remd_employee.remd_id')
                ->innerJoin('employee', 'employee.id = remd_employee.employee_id');

            if ($dateFrom && $dateTo) {
                $uniqueEmployeesWithDocumentsQuery->andWhere(['between', 'remd_alias.registration_date', $dateFrom, $dateTo]);
            }

            if ($documentType) {
                $uniqueEmployeesWithDocumentsQuery->andWhere(['remd_alias.type' => $documentType]);
            }

            if ($positionId) {
                $uniqueEmployeesWithDocumentsQuery->andWhere(['employee.position_id' => $positionId]);
            }

            if ($enabledDocTypes and !$allDocuments) {
                $uniqueEmployeesWithDocumentsQuery->andWhere(['remd_alias.type' => $enabledDocTypes]);
            }

            if ($employeeId) {
                $uniqueEmployeesWithDocumentsQuery->andWhere(['employee.id' => $employeeId]);
            }

            $uniqueEmployeesWithDocuments = $uniqueEmployeesWithDocumentsQuery->count();

            $latestDocumentDate = $baseSettings->date_of_update ? $baseSettings->date_of_update : Remd::getLastRegistrationDate();

            $employeeName = '';

            $positionName = '';

            if ($employeeId) {
                $employee = Employee::findOne($employeeId);
                $employeeName = $employee ? $employee->getFullName() : '';
            }

            if ($employeeId) {
                $position = Position::findOne($positionId);
                $positionName = $position ? $position->name : '';
            }

            $dataToCache = [
                'data' => $result,
                'dataProvider' => $dataProvider,
                'totalDocuments' => $totalDocuments,
                'uniqueDocumentTypes' => $uniqueDocumentTypes,
                'uniqueEmployeesWithDocuments' => $uniqueEmployeesWithDocuments,
                'latestDocumentDate' => $latestDocumentDate,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'documentType' => $documentType,
                'employeeId' => $employeeId,
                'positionId' => $positionId,
                'selectedEmployeeName' => $employeeName,
                'selectedPositionName' => $positionName,
                'enabledDocTypes' => $enabledDocTypes,
            ];

            if ($baseSettings->use_caching) {
                $cache->set($cacheKey, $dataToCache, 0);
            }
        } else {
            $dataToCache = $cachedData;
        }

        return $this->render('index', $dataToCache);
    }

    /**
     * Возвращает список сотрудников в формате JSON для автодополнения.
     * Используется для поиска сотрудников в интерфейсе.
     *
     * @return array Массив результатов в формате ['results' => [['id' => , 'text' => ], ...]]
     */
    public function actionEmployeeList() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $q = $request->get('q');

        $uniqueEmployeeIds = RemdEmployee::find()
            ->select('employee_id')
            ->distinct()
            ->column();

        $query = Employee::find()
            ->select(['employee.id', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name, CONCAT("(", position.name, ")")) AS text'])
            ->joinWith('position')
            ->where(['employee.id' => $uniqueEmployeeIds])
            ->groupBy(['employee.id'])
            ->orderBy('employee.last_name');

        if ($q) {
            $query->andWhere(['like', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name, position.name)', trim($q)]);
        }

        $results = $query->limit(20)->asArray()->all();

        return ['results' => array_map(function($item) {
            return ['id' => $item['id'], 'text' => $item['text']];
        }, $results)];
    }

    /**
     * Возвращает список должностей в формате JSON для автодополнения.
     * Используется для поиска должностей в интерфейсе.
     *
     * @return array Массив результатов в формате ['results' => [['id' => , 'text' => ], ...]]
     */
    public function actionPositionList() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $q = $request->get('q');

        $uniqueEmployeeIds = RemdEmployee::find()
            ->select('employee_id')
            ->distinct()
            ->column();

        $query = Position::find()
            ->select(['position.id', 'position.name AS text'])
            ->joinWith('employees')
            ->where(['employee.id' => $uniqueEmployeeIds])
            ->orderBy('position.name');

        if ($q) {
            $query->andWhere(['like', 'position.name', trim($q)]);
        }

        $results = $query->limit(20)->asArray()->all();

        return ['results' => array_map(function($item) {
            return ['id' => $item['id'], 'text' => $item['text']];
        }, $results)];
    }

    /**
     * Действие для управления базовыми настройками системы.
     * Позволяет настраивать параметры отображения документов.
     *
     * @return string|Response Результат рендеринга или редирект после сохранения
     * @throws Exception Если произошла ошибка при получении или сохранении настроек
     */
    public function actionBaseSetting()
    {
        $model = RemdBaseSetting::getSettings();

        $years = Remd::getUniqueRegistrationYears();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->save()) {
                Yii::$app->notification->success('Настройки успешно сохранены');
                return $this->refresh();
            } else {
                Yii::$app->notification->error('Ошибка при сохранении настроек');
            }
        }

        return $this->render('base_settings', [
            'model' => $model,
            'years' => $years,
        ]);
    }

    /**
     * Действие для управления настройками типов документов.
     * Позволяет выбирать какие типы документов будут отображаться по умолчанию.
     *
     * @return string|Response Результат рендеринга или редирект после сохранения
     * @throws Exception Если произошла ошибка при получении или сохранении настроек
     */
    public function actionTypeSetting()
    {
        $settings = RemdTypeSetting::getSettings();

        $allDocTypes = Remd::getAllDocTypes();

        if (Yii::$app->request->isPost) {
            $selectedTypes = Yii::$app->request->post('doc_types', []);
            $settings->setEnabledDocTypesArray($selectedTypes);

            if ($settings->save()) {
                Yii::$app->notification->success('Настройки успешно сохранены');
            } else {
                Yii::$app->notification->error('Ошибка при сохранении настроек');
            }
            return $this->refresh();
        }

        return $this->render('type_settings', [
            'settings' => $settings,
            'allDocTypes' => $allDocTypes,
        ]);
    }

    /**
     * Отображает список планов РЕМД с группировкой по годам.
     *
     * @return string Результат рендеринга представления
     */
    public function actionPlan()
    {
        $query = RemdPlan::find()
            ->orderBy(['year' => SORT_DESC, 'type' => SORT_ASC]);

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 100,
        ]);

        $models = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $groupedModels = [];
        foreach ($models as $model) {
            $groupedModels[$model->year][] = $model;
        }

        return $this->render('plan', [
            'groupedModels' => $groupedModels,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Создает новый план РЕМД.
     *
     * @return string|Response Результат рендеринга или редирект после сохранения
     */
    public function actionCreatePlan()
    {
        $model = new RemdPlan();
        $docTypes = Remd::getAllDocTypes();
        $years = Remd::getUniqueRegistrationYears();

        if ($this->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save(false)) {
                    Yii::$app->notification->success('План успешно добавлен');
                    return $this->redirect(['plan']);
                } else {
                    Yii::$app->notification->error('Ошибка при сохранении в БД');
                }
            } else {
                Yii::$app->notification->error('Исправьте ошибки в форме');
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create_plan', [
            'model' => $model,
            'docTypes' => $docTypes,
            'years' => $years,
        ]);
    }

    /**
     * Обновляет существующий план РЕМД.
     *
     * @param int $id ID плана для обновления
     * @return string|Response Результат рендеринга или редирект после сохранения
     * @throws NotFoundHttpException Если план не найден
     */
    public function actionUpdatePlan($id)
    {
        $model = RemdPlan::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }

        $docTypes = Remd::getAllDocTypes();
        $years = Remd::getUniqueRegistrationYears();

        if ($this->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save(false)) {
                    Yii::$app->notification->success('План успешно обновлен');
                    return $this->redirect(['plan']);
                } else {
                    Yii::$app->notification->error('Ошибка при сохранении в БД');
                }
            } else {
                Yii::$app->notification->error('Ошибки валидации');
            }
        }

        return $this->render('update_plan', [
            'model' => $model,
            'docTypes' => $docTypes,
            'years' => $years,
        ]);
    }

    /**
     * Удаляет план РЕМД.
     *
     * @param int $id ID плана для удаления
     * @return Response Редирект на страницу списка планов
     * @throws NotFoundHttpException Если план не найден
     */
    public function actionDeletePlan($id) {
        $model = RemdPlan::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Запрошенная страница не существует.');
        }

        if ($model->delete()) {
            Yii::$app->notification->success('План успешно удален');
            return $this->redirect(['plan']);
        } else {
            Yii::$app->notification->error('Не удалось удалить план');
        }

        return $this->redirect(['plan']);
    }

    /**
     * Отображает аналитику по выполнению планов РЕМД.
     * Показывает сравнение плановых и фактических показателей.
     *
     * @return string Результат рендеринга представления
     */
    public function actionAnalytics()
    {
        $model = RemdBaseSetting::getSettings();

        $year = $model->analytics_period ? $model->analytics_period : date('Y');

        $hideEmptyMonths = $model->hide_empty_months;

        $chartType = $model->chart_type;

        $generalPlan = RemdPlan::find()
            ->where(['year' => $year])
            ->andWhere(['or', ['type' => null], ['type' => '']])
            ->one();

        $typedPlans = RemdPlan::find()
            ->where(['year' => $year])
            ->andWhere(['IS NOT', 'type', null])
            ->andWhere(['<>', 'type', ''])
            ->all();

        $generalActual = Remd::getActualStats($year);

        $data = [
            'general' => [
                'plan' => $generalPlan,
                'actual' => $generalActual,
            ],
            'typed' => [],
            'hideEmptyMonths' => $hideEmptyMonths,
        ];

        foreach ($typedPlans as $plan) {
            if (!empty($plan->type)) {
                $data['typed'][] = [
                    'plan' => $plan,
                    'actual' => Remd::getActualStats($year, $plan->type),
                    'type' => $plan->type,
                ];
            }
        }

        return $this->render('analytics', [
            'year' => $year,
            'data' => $data,
            'chartType' => $chartType,
        ]);
    }

    /**
     * Очищает кэш.
     */
    public function actionFlushCache()
    {
        if (Yii::$app->cache->flush()) {
            Yii::$app->notification->success('Кэш очищен');
        } else {
            Yii::$app->notification->error('Не удалось очистить кэш');
        }

        return $this->redirect(['index']);
    }
}