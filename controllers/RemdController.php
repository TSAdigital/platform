<?php

namespace app\controllers;

use app\models\Employee;
use app\models\Position;
use app\models\Remd;
use app\models\RemdBaseSetting;
use app\models\RemdTypeSetting;
use Yii;
use yii\data\Pagination;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class RemdController extends Controller
{
    /**
     * Определяет поведения и правила доступа для контроллера.
     *
     * Наследует поведения родительского класса и добавляет:
     * - AccessControl для управления правами доступа к экшенам
     *
     * Правила доступа:
     * - Экшены 'index' и 'employee-list' доступны пользователям с ролью 'viewRemdList'
     * - Экшены 'base-setting' и 'type-setting' доступны пользователям с ролью 'makeRemdSetting'
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
                            'actions' => ['index', 'employee-list'],
                            'roles' => ['viewRemdList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['base-setting', 'type-setting'],
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

        $lastRegistrationDate = $baseSettings->date_of_update ? $baseSettings->date_of_update : Remd::getLastRegistrationDate();

        $currentYear = date('Y');
        $defaultDateFrom = $baseSettings->date_from ? date('Y-m-d', strtotime($baseSettings->date_from)) : $currentYear . '-01-01';
        $defaultDateTo = $baseSettings->date_to ? date('Y-m-d', strtotime($baseSettings->date_to)) : $currentYear . '-12-31';

        $searchParams = Yii::$app->request->get();
        $employeeId = $searchParams['employee_id'] ?? '';
        $positionId = $searchParams['position_id'] ?? '';
        $dateFrom = (isset($searchParams['date_from']) && !empty($searchParams['date_from'])) ? date('Y-m-d', strtotime($searchParams['date_from'])) : $defaultDateFrom;
        $dateTo = (isset($searchParams['date_to']) && !empty($searchParams['date_to'])) ? date('Y-m-d', strtotime($searchParams['date_to'])) : $defaultDateTo;
        $docType = $searchParams['doc_type'] ?? '';
        $allDocuments = $searchParams['all_documents'] ?? null;

        $employeeName = '';

        if ($employeeId) {
            $employee = Employee::findOne($employeeId);
            $employeeName = $employee ? $employee->getFullName() : '';
        }


        $enabledDocTypes = $typeSettings->getEnabledDocTypesArray();

        $allDocTypes = Remd::find()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->column();

        if ($enabledDocTypes and !$allDocuments) {
            $allDocTypes = $enabledDocTypes;
        }

        $filteredRemdsQuery = Remd::find()
            ->joinWith([
                'employees' => function($query) use ($positionId, $employeeId) {
                    if (!empty($positionId)) {
                        $query->andWhere(['employee.position_id' => $positionId]);
                    }
                    if (!empty($employeeId)) {
                        $query->andWhere(['employee.id' => $employeeId]);
                    }
                }
            ]);

        if ($enabledDocTypes and !$allDocuments) {
            $filteredRemdsQuery->andWhere(['remd.type' => $enabledDocTypes]);
        }

        if (!empty($dateFrom)) {
            $filteredRemdsQuery->andWhere(['>=', 'remd.registration_date', $dateFrom]);
        }
        if (!empty($dateTo)) {
            $filteredRemdsQuery->andWhere(['<=', 'remd.registration_date', $dateTo]);
        }
        if (!empty($docType)) {
            $filteredRemdsQuery->andWhere(['remd.type' => $docType]);
        }

        $totalRemds = (clone $filteredRemdsQuery)
            ->select('remd.id')
            ->distinct()
            ->count();

        $filteredTypesQuery = (clone $filteredRemdsQuery)
            ->select('type')
            ->distinct();

        if (!empty($positionId)) {
            $filteredTypesQuery->andWhere(['employee.position_id' => $positionId]);
        }

        $filteredTypes = $filteredTypesQuery->column();
        $filteredTypesCount = count($filteredTypes);

        $remdsByType = (clone $filteredRemdsQuery)
            ->select(['type', 'COUNT(*) as count'])
            ->groupBy('type')
            ->asArray()
            ->all();

        $employeesQuery = Employee::find()
            ->joinWith([
                'remds' => function($query) use ($dateFrom, $dateTo, $docType) {
                    $query->andWhere(['IS NOT', 'remd.id', null]);
                    if (!empty($dateFrom)) {
                        $query->andWhere(['>=', 'remd.registration_date', $dateFrom]);
                    }
                    if (!empty($dateTo)) {
                        $query->andWhere(['<=', 'remd.registration_date', $dateTo]);
                    }
                    if (!empty($docType)) {
                        $query->andWhere(['remd.type' => $docType]);
                    }
                },
                'position'
            ])->groupBy('employee.id');

        if ($enabledDocTypes and !$allDocuments) {
            $employeesQuery->andWhere(['remd.type' => $enabledDocTypes]);
        }

        if (!empty($positionId)) {
            $employeesQuery->andWhere(['position_id' => $positionId]);
        }
        if (!empty($employeeId)) {
            $employeesQuery->andWhere(['employee.id' => $employeeId]);
        }

        $totalEmployeesWithRemds = (clone $employeesQuery)
            ->select('employee.id')
            ->distinct()
            ->count();

        $employeesQuery->orderBy([
            'last_name' => SORT_ASC,
            'first_name' => SORT_ASC,
            'middle_name' => SORT_ASC,
            'id' => SORT_ASC
        ]);

        $pages = new Pagination([
            'totalCount' => $totalEmployeesWithRemds,
            'pageSize' => $baseSettings->page_size ? $baseSettings->page_size : 10,
            'pageSizeParam' => false,
        ]);

        $employeesWithRemds = $employeesQuery
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        $query = Position::find()
            ->select(['position.id', 'position.name'])
            ->innerJoin('employee', 'employee.position_id = position.id')
            ->innerJoin('remd_employee', 'remd_employee.employee_id = employee.id')
            ->innerJoin('remd', 'remd.id = remd_employee.remd_id')
            ->distinct();

        if (!empty($enabledDocTypes) and !$allDocuments) {
            $query->where(['remd.type' => $enabledDocTypes]);
        }

        $positions = $query->asArray()->all();

        $positionList = ArrayHelper::map($positions, 'id', 'name');

        return $this->render('index', [
            'totalRemds' => $totalRemds,
            'remdsByType' => $remdsByType,
            'employeesWithRemds' => $employeesWithRemds,
            'pages' => $pages,
            'totalTypesCount' => $filteredTypesCount,
            'totalEmployeesWithRemds' => $totalEmployeesWithRemds,
            'allDocTypes' => $allDocTypes,
            'docType' => $docType,
            'positionList' => $positionList,
            'employeeId' => $employeeId,
            'selectedEmployeeName' => $employeeName,
            'positionId' => $positionId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'enabledDocTypes' => $enabledDocTypes,
            'allDocuments' => $allDocuments,
            'lastRegistrationDate' => $lastRegistrationDate,
        ]);
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
        $enabledDocTypesJson = $request->get('enabledDocTypes');

        $enabledDocTypes = [];
        if ($enabledDocTypesJson) {
            $enabledDocTypes = json_decode($enabledDocTypesJson, true);
        }

        $query = Employee::find()
            ->select(['employee.id', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name, CONCAT("(", position.name, ")")) AS text'])
            ->joinWith('position')
            ->innerJoinWith('remds')
            ->groupBy(['employee.id'])
            ->orderBy('employee.last_name');

        if ($q) {
            $query->where(['like', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name, position.name)', trim($q)]);
        }

        if (!empty($enabledDocTypes)) {
            $query->andWhere(['remd.type' => $enabledDocTypes]);
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

        if ($model->load(Yii::$app->request->post())) {

            if ($model->save()) {
                Yii::$app->notification->success('Настройки успешно сохранены');
                return $this->refresh();
            } else {
                Yii::$app->notification->error('Ошибка при сохранении настроек');
            }
        }

        return $this->render('base_setting', [
            'model' => $model,
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
        $allDocTypes = Remd::find()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->column();

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
}