<?php

namespace app\commands;

use app\models\Employee;
use app\models\Remd;
use app\models\RemdEmployee;
use moonland\phpexcel\Excel;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

class RemdController extends Controller
{
    const REQUIRED_COLUMNS = [
        'Идентификатор записи',
        'Вид документа',
        'Дата регистрации',
        'Подписавшие сотрудники'
    ];
    const FILE_PATH = '@app/web/import/remd.xlsx';

    /**
     * Проверяет файл перед импортом
     *
     * @return int Код завершения (ExitCode::OK в случае успеха)
     */
    public function actionCheck()
    {
        $this->stdout("=== ПРОВЕРКА ФАЙЛА ===\n", BaseConsole::FG_YELLOW);
        try {
            $data = $this->loadAndValidateFile();
            $this->checkEmployees($data);
            $this->stdout("\nПРОВЕРКА УСПЕШНО ЗАВЕРШЕНА. Можно выполнять импорт.\n", BaseConsole::FG_GREEN);
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("\nОШИБКА: " . $e->getMessage() . "\n", BaseConsole::FG_RED);
            return ExitCode::DATAERR;
        }
    }

    /**
     * Импортирует данные из файла в базу данных
     */
    public function actionImport()
    {
        $startTime = time();

        $this->stdout("=== НАЧАЛО ИМПОРТА ===\n", BaseConsole::FG_YELLOW);

        $data = $this->loadAndValidateFile();

        $employeesMap = $this->loadAllEmployeesMap();

        $success = 0;
        $skip = 0;
        $errors = 0;

        foreach ($data as $datum) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $remd = new Remd();
                $remd->unique_code = trim($datum['Идентификатор записи']);
                $remd->registration_date = $this->processDate($datum['Дата регистрации']);
                $remd->type = $this->processDocumentType($datum['Вид документа']);

                if ($remd->save()) {
                    $employees = explode(';', $datum['Подписавшие сотрудники']);
                    $employees = array_map('trim', $employees);
                    $employees = array_unique($employees);

                    $allEmployeesSaved = true;
                    foreach ($employees as $employee) {
                        $remdEmployee = new RemdEmployee();
                        $remdEmployee->remd_id = $remd->id;
                        $remdEmployee->employee_id = $this->getEmployeeId($employee, $employeesMap);

                        if (!$remdEmployee->save()) {
                            $allEmployeesSaved = false;
                            break;
                        }
                    }

                    if ($allEmployeesSaved) {
                        $transaction->commit();
                        $success++;
                    } else {
                        $transaction->rollBack();
                        $errors++;
                    }
                } else {
                    $transaction->rollBack();
                    $skip++;
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                $errors++;
            }
        }

        $endTime = time();
        $duration = ($endTime - $startTime) / 60;

        $this->stdout("Импортировано: $success документов\n", BaseConsole::FG_GREEN);
        $this->stdout("Пропущено: $skip документов\n", BaseConsole::FG_YELLOW);
        $this->stdout("Ошибок: $errors документов\n", BaseConsole::FG_RED);
        $this->stdout("Время выполнения: " . round($duration, 2) . " мин\n", BaseConsole::FG_BLUE);

        $this->stdout("=== ИМПОРТ ЗАВЕРШЕН ===\n", BaseConsole::FG_YELLOW);
    }

    /**
     * Получает идентификатор сотрудника
     *
     * @return integer|null
     */
    private function getEmployeeId($employee, $employeesMap) {
        $value = $employeesMap[$employee];
        return $value ?? null;
    }

    /**
     * Загружает и проверяет файл Excel
     *
     * @return array Данные из файла
     * @throws \Exception Если файл не найден, пуст или отсутствуют обязательные колонки
     */
    protected function loadAndValidateFile()
    {
        $filePath = \Yii::getAlias(self::FILE_PATH);
        if (!file_exists($filePath)) {
            throw new \Exception("Файл не найден: {$filePath}");
        }

        $data = Excel::import($filePath, [
            'setFirstRecordAsKeys' => true,
        ]);

        if (empty($data)) {
            throw new \Exception("Файл пуст или не может быть прочитан");
        }

        $missingColumns = [];
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (!isset($data[0][$column])) {
                $missingColumns[] = $column;
            }
        }

        if (!empty($missingColumns)) {
            throw new \Exception("Отсутствуют обязательные столбцы: " . implode(', ', $missingColumns));
        }

        return $data;
    }

    /**
     * Проверяет сотрудников
     *
     * @param array $data Данные из файла
     * @throws \Exception Если найдены проблемы с сотрудниками
     */
    protected function checkEmployees($data)
    {
        $employeesInfo = $this->collectEmployeesInfo($data);
        if (!empty($employeesInfo['missing'])) {
            $this->stderr("\nНАЙДЕНЫ ПРОБЛЕМЫ:\n", BaseConsole::FG_RED);
            foreach ($employeesInfo['missing'] as $employeeStr => $error) {
                $this->stderr("- {$employeeStr}: {$error}\n", BaseConsole::FG_RED);
            }
            throw new \Exception(sprintf(
                "Обнаружены проблемы с сотрудниками: %d",
                count($employeesInfo['missing'])
            ));
        }

        $this->stdout(sprintf(
            "Все сотрудники в порядке: проверено %d сотрудников\n",
            $employeesInfo['unique']
        ), Console::FG_GREEN);
    }

    /**
     * Собирает информацию о сотрудниках
     *
     * @param array $data Данные из файла
     * @return array Массив с информацией о сотрудниках
     */
    protected function collectEmployeesInfo($data)
    {
        $result = [
            'total' => 0,
            'unique' => 0,
            'missing' => []
        ];

        $allEmployees = $this->loadAllEmployeesMap();
        $processedEmployees = [];

        foreach ($data as $row) {
            if (empty($row['Подписавшие сотрудники'])) continue;

            $employees = array_map('trim', explode(';', $row['Подписавшие сотрудники']));
            foreach ($employees as $employeeStr) {
                $result['total']++;
                $employeeKey = md5($employeeStr);

                if (isset($processedEmployees[$employeeKey])) continue;

                $processedEmployees[$employeeKey] = true;
                $result['unique']++;

                try {
                    $this->parseEmployeeFast($employeeStr, $allEmployees);
                } catch (\Exception $e) {
                    $result['missing'][$employeeStr] = $e->getMessage();
                }
            }
        }

        return $result;
    }

    /**
     * Быстрая проверка сотрудника по предзагруженному массиву
     *
     * @param string $employeeStr Строка в формате "Фамилия Имя Отчество ДД.ММ.ГГГГ"
     * @param array $employeesMap Ассоциативный массив всех сотрудников
     * @return int ID сотрудника
     * @throws \Exception
     */
    protected function parseEmployeeFast($employeeStr, $employeesMap)
    {
        $searchStr = trim(preg_replace('/\s+/u', ' ', $employeeStr));

        if (!preg_match('/^(.+)\s(\d{2}\.\d{2}\.\d{4})$/', $searchStr, $matches)) {
            throw new \Exception("Неверный формат: {$searchStr}");
        }

        if (!isset($employeesMap[$searchStr])) {
            throw new \Exception("Сотрудник не найден: {$searchStr}");
        }

        return $employeesMap[$searchStr];
    }

    /**
     * Загружает всех сотрудников из базы в виде ассоциативного массива
     *
     * @return array
     */
    protected function loadAllEmployeesMap()
    {
        $employeesMap = [];

        $query = Employee::find()
            ->select(['id', 'last_name', 'first_name', 'middle_name', 'birth_date'])
            ->asArray();

        foreach ($query->batch(1000) as $batch) {
            foreach ($batch as $employee) {
                $key = implode(' ', [
                    $employee['last_name'],
                    $employee['first_name'],
                    $employee['middle_name'],
                    \Yii::$app->formatter->asDate($employee['birth_date'], 'dd.MM.yyyy')
                ]);
                $key = trim(preg_replace('/\s+/u', ' ', $key));
                $employeesMap[$key] = $employee['id'];
            }
        }

        return $employeesMap;
    }

    /**
     * Обрабатывает тип документа
     *
     * @param string $type Тип документа
     * @return string
     */
    protected function processDocumentType($type)
    {
        $pattern = '/\s*(?:\(CDA\)|\(PDF\/A-1\)).*$/i';
        $type = preg_replace($pattern, '', $type);
        return trim($type);
    }

    /**
     * Преобразует дату в формат Y-m-d
     *
     * @param mixed $date Дата
     * @return string
     * @throws \Exception
     */
    protected function processDate($date)
    {
        if (is_numeric($date)) {
            return date('Y-m-d', $date);
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new \Exception("Неверный формат даты: {$date}");
        }

        return date('Y-m-d', $timestamp);
    }
}