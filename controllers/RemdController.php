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
                            'actions' => ['index', 'get-stats', 'get-document-types-stats', 'employee-document-types', 'employee-list', 'position-list', 'analytics', 'employee-documents'],
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
     * Главное действие контроллера, отображающее список документов РЕМД.
     * Позволяет фильтровать документы по дате, типу, сотруднику и должности.
     *
     * @return string Результат рендеринга представления с фильтрами и данными
     */
    public function actionIndex()
    {
        $currentYear = date('Y');

        $baseSettings = RemdBaseSetting::getSettings();
        $typeSettings = RemdTypeSetting::getSettings();

        $searchParams = Yii::$app->request->get();

        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';
        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $documentType = $searchParams['document_type'] ?? null;
        $employeeId = $searchParams['employee_id'] ?? null;
        $positionId = $searchParams['position_id'] ?? null;

        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        $limit = $baseSettings->page_size ? $baseSettings->page_size : 20;

        $uniqueDocumentTypes = Remd::find()
            ->select(['type'])
            ->distinct()
            ->orderBy(['type' => SORT_ASC])
            ->column();

        if ($employeeId) {
            $employee = Employee::findOne($employeeId);
            $employeeName = $employee ? $employee->getFullName() : '';
        } else {
            $employeeName = '';
        }

        if ($positionId) {
            $position = Position::findOne($positionId);
            $positionName = $position ? $position->name : '';
        } else {
            $positionName = '';
        }

        return $this->render('index', [
            'uniqueDocumentTypes' => $uniqueDocumentTypes,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'documentType' => $documentType,
            'employeeId' => $employeeId,
            'positionId' => $positionId,
            'selectedEmployeeName' => $employeeName,
            'selectedPositionName' => $positionName,
            'enabledDocTypes' => $enabledDocTypes,
            'limit' => $limit,
        ]);
    }

    /**
     * Возвращает статистику по документам в формате JSON.
     * Содержит общее количество документов, уникальных типов и сотрудников.
     * Поддерживает фильтрацию по дате, типу документа, сотруднику и должности.
     *
     * @return array JSON-ответ с данными статистики
     */
    public function actionGetStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $currentYear = date('Y');

        $baseSettings = RemdBaseSetting::getSettings();
        $typeSettings = RemdTypeSetting::getSettings();

        $searchParams = Yii::$app->request->get();

        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';

        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $documentType = $searchParams['document_type'] ?? null;
        $employeeId = $searchParams['employee_id'] ?? null;
        $positionId = $searchParams['position_id'] ?? null;
        $allDocuments = $searchParams['all_documents'] ?? null;

        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        if ($allDocuments == 1) {
            $enabledDocTypes = null;
        }

        $cacheKey = [
            'remd_get_stats',
            'currentYear' => $currentYear,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'documentType' => $documentType,
            'employeeId' => $employeeId,
            'positionId' => $positionId,
            'allDocuments' => $allDocuments,
            'enabledDocTypes' => $enabledDocTypes,
        ];

        $cacheKey = md5(serialize($cacheKey));

        $cache = Yii::$app->cache;
        $cachedData = $cache->get($cacheKey);

        if ($cachedData === false) {
            $query = Remd::find()
                ->select([
                    'total_documents' => 'COUNT(DISTINCT remd.id)',
                    'unique_types_count' => 'COUNT(DISTINCT remd.type)',
                ])
                ->andWhere(['>=', 'remd.registration_date', $dateFrom])
                ->andWhere(['<=', 'remd.registration_date', $dateTo])
                ->andFilterWhere(['remd.type' => $enabledDocTypes]);

            $employeesQuery = (new \yii\db\Query())
                ->select(['COUNT(DISTINCT remd_employee.employee_id)'])
                ->from('remd_employee')
                ->andWhere(['>=', 'remd.registration_date', $dateFrom])
                ->andWhere(['<=', 'remd.registration_date', $dateTo])
                ->andFilterWhere(['remd.type' => $enabledDocTypes])
                ->innerJoin('remd', 'remd.id = remd_employee.remd_id');

            if (!empty($searchParams['document_type'])) {
                $query->andWhere(['remd.type' => $searchParams['document_type']]);
                $employeesQuery->andWhere(['remd.type' => $searchParams['document_type']]);
            }

            if (!empty($searchParams['date_from'])) {
                $query->andWhere(['>=', 'remd.registration_date', $dateFrom]);
                $employeesQuery->andWhere(['>=', 'remd.registration_date', $dateFrom]);
            }

            if (!empty($searchParams['date_to'])) {
                $query->andWhere(['<=', 'remd.registration_date', $dateTo]);
                $employeesQuery->andWhere(['<=', 'remd.registration_date', $dateTo]);
            }

            if (!empty($employeeId) || !empty($positionId)) {
                $query->innerJoin('remd_employee', 'remd_employee.remd_id = remd.id');

                if (!empty($employeeId)) {
                    $query->andWhere(['remd_employee.employee_id' => $employeeId]);
                    $employeesQuery->andWhere(['remd_employee.employee_id' => $employeeId]);
                }

                if (!empty($positionId)) {
                    $query->innerJoin('employee', 'employee.id = remd_employee.employee_id')
                        ->andWhere(['employee.position_id' => $positionId]);
                    $employeesQuery->innerJoin('employee', 'employee.id = remd_employee.employee_id')
                        ->andWhere(['employee.position_id' => $positionId]);
                }
            }

            $result = $query->asArray()->one();
            $employeesCount = $employeesQuery->scalar();
            $updateDate = $baseSettings->date_of_update ? date('Y-m-d', strtotime($baseSettings->date_of_update)) : Remd::getLastRegistrationDate();

            $dataToCache = [
                'allDocumentCount' => $result['total_documents'] ?? 0,
                'allTypesCount' => $result['unique_types_count'] ?? 0,
                'allEmployeesCount' => $employeesCount ?? 0,
                'updateDate' => $updateDate,
            ];

            if ($baseSettings->use_caching) {
                $cache->set($cacheKey, $dataToCache, 0);
            }

        }else {
            $dataToCache = $cachedData;
        }

        return $dataToCache;
    }

    /**
     * Возвращает статистику по типам документов в формате JSON.
     * Содержит количество документов каждого типа.
     * Поддерживает фильтрацию по дате, типу документа, сотруднику и должности.
     *
     * @return array JSON-ответ с данными по типам документов
     */
    public function actionGetDocumentTypesStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $currentYear = date('Y');

        $baseSettings = RemdBaseSetting::getSettings();
        $typeSettings = RemdTypeSetting::getSettings();

        $searchParams = Yii::$app->request->get();

        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';
        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $documentType = $searchParams['document_type'] ?? null;
        $employeeId = $searchParams['employee_id'] ?? null;
        $positionId = $searchParams['position_id'] ?? null;
        $allDocuments = $searchParams['all_documents'] ?? null;

        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        if ($allDocuments == 1) {
            $enabledDocTypes = null;
        }

        $cacheKey = [
            'remd_types_stats',
            'currentYear' => $currentYear,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'documentType' => $documentType,
            'employeeId' => $employeeId,
            'positionId' => $positionId,
            'allDocuments' => $allDocuments,
            'enabledDocTypes' => $enabledDocTypes,
        ];

        $cacheKey = md5(serialize($cacheKey));

        $cache = Yii::$app->cache;
        $cachedData = $cache->get($cacheKey);

        if ($cachedData === false) {
            $query = Remd::find()
                ->select([
                    'type',
                    'count' => 'COUNT(*)'
                ])
                ->andWhere(['>=', 'remd.registration_date', $dateFrom])
                ->andWhere(['<=', 'remd.registration_date', $dateTo])
                ->andFilterWhere(['remd.type' => $enabledDocTypes])
                ->groupBy(['type'])
                ->orderBy(['type' => SORT_ASC]);

            if (!empty($documentType)) {
                $query->andWhere(['remd.type' => $documentType]);
            }

            if (!empty($dateFrom)) {
                $query->andWhere(['>=', 'remd.registration_date', $dateFrom]);
            }

            if (!empty($dateTo)) {
                $query->andWhere(['<=', 'remd.registration_date', $dateTo]);
            }

            if ($employeeId || $positionId) {
                $query->innerJoin('remd_employee', 'remd_employee.remd_id = remd.id');

                if ($employeeId) {
                    $query->andWhere(['remd_employee.employee_id' => $employeeId]);
                }

                if ($positionId) {
                    $query->innerJoin('employee', 'employee.id = remd_employee.employee_id')
                        ->andWhere(['employee.position_id' => $positionId]);
                }
            }

            $dataToCache = $query->asArray()->all();

            if ($baseSettings->use_caching) {
                $cache->set($cacheKey, $dataToCache, 0);
            }

        } else {
            $dataToCache = $cachedData;
        }

        return $dataToCache;
    }

    /**
     * Возвращает список документов по сотрудникам в формате JSON.
     * Содержит информацию о количестве документов у каждого сотрудника.
     * Поддерживает пагинацию и фильтрацию по дате, типу документа и должности.
     *
     * @return array JSON-ответ с данными по сотрудникам и их документам
     */
    public function actionEmployeeDocuments()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $currentYear = date('Y');

        $baseSettings = RemdBaseSetting::getSettings();
        $typeSettings = RemdTypeSetting::getSettings();

        $searchParams = Yii::$app->request->get();

        $limit = $baseSettings->page_size ? $baseSettings->page_size : 20;

        $offset = Yii::$app->request->get('offset', 0);
        $limit = Yii::$app->request->get('limit', $limit);

        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';

        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $documentType = $searchParams['document_type'] ?? null;
        $employeeId = $searchParams['employee_id'] ?? null;
        $positionId = $searchParams['position_id'] ?? null;
        $allDocuments = $searchParams['all_documents'] ?? null;

        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        if ($allDocuments == 1) {
            $enabledDocTypes = null;
        }

        $cacheKey = [
            'remd_employee_stats',
            'currentYear' => $currentYear,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'documentType' => $documentType,
            'employeeId' => $employeeId,
            'positionId' => $positionId,
            'allDocuments' => $allDocuments,
            'enabledDocTypes' => $enabledDocTypes,
            'offset' => $offset,
            'limit' => $limit,
        ];

        $cacheKey = md5(serialize($cacheKey));

        $cache = Yii::$app->cache;
        $cachedData = $cache->get($cacheKey);

        if ($cachedData === false) {
            $query = Employee::find()
                ->select([
                    'employee.id',
                    'employee.last_name',
                    'employee.first_name',
                    'employee.middle_name',
                    'position.name as position_name',
                    'document_count' => 'COUNT(DISTINCT remd.id)'
                ])
                ->andWhere(['>=', 'remd.registration_date', $dateFrom])
                ->andWhere(['<=', 'remd.registration_date', $dateTo])
                ->andFilterWhere(['remd.type' => $enabledDocTypes])
                ->joinWith('position')
                ->innerJoin('remd_employee', 'remd_employee.employee_id = employee.id')
                ->innerJoin('remd', 'remd.id = remd_employee.remd_id')
                ->groupBy(['employee.id', 'position.name'])
                ->orderBy([
                    'employee.last_name' => SORT_ASC,
                    'employee.first_name' => SORT_ASC,
                    'employee.middle_name' => SORT_ASC
                ]);

            // Применяем фильтры
            if (!empty($searchParams['document_type'])) {
                $query->andWhere(['remd.type' => $documentType]);
            }

            if (!empty($dateFrom)) {
                $query->andWhere(['>=', 'remd.registration_date', $dateFrom]);
            }

            if (!empty($dateTo)) {
                $query->andWhere(['<=', 'remd.registration_date', $dateTo]);
            }

            if (!empty($employeeId)) {
                $query->andWhere(['employee.id' => $employeeId]);
            }

            if (!empty($positionId)) {
                $query->andWhere(['employee.position_id' => $positionId]);
            }

            $totalCount = $query->count();
            $employees = $query->offset($offset)->limit($limit)->asArray()->all();

            $dataToCache = [
                'employees' => $employees,
                'totalCount' => $totalCount,
                'hasMore' => ($offset + $limit) < $totalCount,
                'limit' => $limit
            ];

            if ($baseSettings->use_caching) {
                $cache->set($cacheKey, $dataToCache, 0);
            }

        } else {
            $dataToCache = $cachedData;
        }

        return $dataToCache;
    }

    /**
     * Возвращает статистику по типам документов для конкретного сотрудника в формате JSON.
     * Содержит количество документов каждого типа у указанного сотрудника.
     * Поддерживает фильтрацию по дате и типу документа.
     *
     * @return array JSON-ответ с данными по типам документов сотрудника
     */
    public function actionEmployeeDocumentTypes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $currentYear = date('Y');

        $baseSettings = RemdBaseSetting::getSettings();
        $typeSettings = RemdTypeSetting::getSettings();

        $searchParams = Yii::$app->request->get();

        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';
        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $employeeId = $searchParams['selected_employee_id'] ?? null;
        $documentType = $searchParams['document_type'] ?? null;
        $positionId = $searchParams['position_id'] ?? null;
        $allDocuments = $searchParams['all_documents'] ?? null;

        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        if ($allDocuments == 1) {
            $enabledDocTypes = null;
        }

        if (!$employeeId) {
            return ['error' => 'Не указан сотрудник'];
        }

        try {
            $query = Remd::find()
                ->select([
                    'type',
                    'count' => 'COUNT(*)',
                    'last_date' => 'MAX(remd.registration_date)' // Добавим дату последнего документа
                ])
                ->innerJoin('remd_employee', 'remd_employee.remd_id = remd.id')
                ->where(['remd_employee.employee_id' => $employeeId])
                ->andWhere(['>=', 'remd.registration_date', $dateFrom])
                ->andWhere(['<=', 'remd.registration_date', $dateTo])
                ->andFilterWhere(['remd.type' => $enabledDocTypes])
                ->groupBy(['type'])
                ->orderBy(['type' => SORT_ASC]);

            // Применяем фильтры
            if (!empty($documentType)) {
                $query->andWhere(['remd.type' => $documentType]);
            }

            if (!empty($dateFrom)) {
                $query->andWhere(['>=', 'remd.registration_date', $dateFrom]);
            }

            if (!empty($dateTo)) {
                $query->andWhere(['<=', 'remd.registration_date', $dateTo]);
            }

            if (!empty($positionId)) {
                $query->innerJoin('employee', 'employee.id = remd_employee.employee_id')
                    ->andWhere(['employee.position_id' => $positionId]);
            }

            $stats = $query->asArray()->all();

            // Добавим информацию о сотруднике
            $employee = Employee::find()
                ->select(['last_name', 'first_name', 'middle_name'])
                ->where(['id' => $employeeId])
                ->asArray()
                ->one();

            return [
                'stats' => $stats,
                'employee' => $employee,
                'total' => array_sum(array_column($stats, 'count'))
            ];

        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'app');
            return ['error' => 'Ошибка при получении данных'];
        }
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

        $cacheKey = [
            'remd_analytics',
            'year' => $year,
            'hideEmptyMonths' => $hideEmptyMonths,
            'chartType' => $chartType,
        ];

        $cacheKey = md5(serialize($cacheKey));

        $cache = Yii::$app->cache;
        $cachedData = $cache->get($cacheKey);

        if ($cachedData === false) {
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

            $dataToCache = [
                'year' => $year,
                'data' => $data,
                'chartType' => $chartType,
            ];

            if ($model->use_caching) {
                $cache->set($cacheKey, $dataToCache, 0);
            }
        } else {
            $dataToCache = $cachedData;
        }

        return $this->render('analytics', $dataToCache);
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