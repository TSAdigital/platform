<?php

namespace app\commands;

use app\models\Employee;
use app\models\Remd;
use app\models\RemdEmployee;
use moonland\phpexcel\Excel;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\ActiveRecord;
use yii\db\Exception;
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
     * Проверяет:
     * - наличие файла
     * - наличие обязательных колонок
     * - корректность данных сотрудников
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
     *
     * @return int Код завершения (ExitCode::OK в случае успеха)
     */
    public function actionImport()
    {
        $this->stdout("=== ИМПОРТ ДАННЫХ ===\n", BaseConsole::FG_YELLOW);

        try {
            $data = $this->loadAndValidateFile();
            $report = $this->importData($data);
            $this->printReport($report);

            return empty($report['errors']) ? ExitCode::OK : ExitCode::SOFTWARE;

        } catch (\Exception $e) {
            $this->stderr("\nФАТАЛЬНАЯ ОШИБКА: " . $e->getMessage() . "\n", BaseConsole::FG_RED);
            return ExitCode::IOERR;
        }
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
                "Обнаружены проблемы с сотрудниками: %d из %d",
                count($employeesInfo['missing']),
                $employeesInfo['total']
            ));
        }

        $this->stdout(sprintf(
            "Все сотрудники в порядке: проверено %d уникальных сотрудников\n",
            $employeesInfo['unique']
        ), Console::FG_GREEN);
    }

    /**
     * Собирает информацию о сотрудниках
     *
     * @param array $data Данные из файла
     * @return array Массив с информацией о сотрудниках:
     *              - total: общее количество упоминаний
     *              - unique: количество уникальных сотрудников
     *              - missing: проблемы с сотрудниками (если есть)
     */
    protected function collectEmployeesInfo($data)
    {
        $result = [
            'total' => 0,
            'unique' => 0,
            'missing' => []
        ];

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
                    $this->parseEmployee($employeeStr);
                } catch (\Exception $e) {
                    $result['missing'][$employeeStr] = $e->getMessage();
                }
            }
        }

        return $result;
    }

    /**
     * Импортирует данные в базу
     *
     * @param array $data Данные для импорта
     * @return array Отчет об импорте:
     *              - imported: количество успешно импортированных записей
     *              - skipped: количество пропущенных записей (дубликаты)
     *              - errors: ошибки импорта
     */
    protected function importData($data)
    {
        $report = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($data as $row) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if (Remd::find()->where(['unique_code' => $row['Идентификатор записи']])->exists()) {
                    $report['skipped']++;
                    $transaction->rollBack();
                    continue;
                }

                $remd = new Remd([
                    'unique_code' => $row['Идентификатор записи'],
                    'type' => $this->processDocumentType($row['Вид документа']),
                    'registration_date' => $this->processDate($row['Дата регистрации']),
                ]);

                if (!$remd->save()) {
                    throw new \Exception("Ошибка сохранения REMD: " . implode(', ', $remd->getFirstErrors()));
                }

                if (!empty($row['Подписавшие сотрудники'])) {
                    $this->processEmployees($row['Подписавшие сотрудники'], $remd->id);
                }

                $transaction->commit();
                $report['imported']++;

            } catch (\Exception $e) {
                $transaction->rollBack();
                $errorMessage = $e->getMessage();

                if (strpos($errorMessage, 'Сотрудник не найден') !== false) {
                    $employeeStr = $this->extractEmployeeFromError($row['Подписавшие сотрудники']);
                    $report['errors'][$row['Идентификатор записи']] = "Сотрудник не найден: " . $employeeStr;
                } else {
                    $report['errors'][$row['Идентификатор записи']] = $errorMessage;
                }
            }
        }

        return $report;
    }

    /**
     * Обрабатывает сотрудников для связи с РЕМД
     *
     * @param string $employeesString Строка с сотрудниками (разделены точкой с запятой)
     * @param int $remdId ID РЕМД записи
     * @throws Exception Если возникла ошибка при сохранении связи
     */
    protected function processEmployees($employeesString, $remdId)
    {
        $employees = array_map('trim', explode(';', $employeesString));

        foreach ($employees as $employeeStr) {
            $employee = $this->parseEmployee($employeeStr, true);

            $relation = new RemdEmployee([
                'remd_id' => $remdId,
                'employee_id' => $employee->id,
            ]);

            if (!$relation->save()) {
                throw new \Exception("Ошибка сохранения связи: " . implode(', ', $relation->getFirstErrors()));
            }
        }
    }

    /**
     * Разбирает строку с информацией о сотруднике
     *
     * Формат строки: "Фамилия Имя Отчество ДД.ММ.ГГГГ"
     *
     * @param string $employeeStr Строка с информацией о сотруднике
     * @param bool $returnObject Если true, возвращает объект Employee, иначе bool
     * @return array|bool|ActiveRecord
     * @throws \Exception Если неверный формат или сотрудник не найден
     */
    protected function parseEmployee($employeeStr, $returnObject = false)
    {
        $parts = preg_split('/\s+/', trim($employeeStr));

        if (count($parts) < 2) {
            throw new \Exception("Неверный формат данных сотрудника");
        }

        $birthDateStr = null;
        foreach ([3, 2] as $position) {
            if (isset($parts[$position]) && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $parts[$position])) {
                $birthDateStr = $parts[$position];
                unset($parts[$position]);
                break;
            }
        }

        if (!$birthDateStr) {
            throw new \Exception("Не найдена дата рождения");
        }

        $birthDate = \DateTime::createFromFormat('d.m.Y', $birthDateStr);
        if (!$birthDate) {
            throw new \Exception("Неверный формат даты рождения");
        }
        $birthDate = $birthDate->format('Y-m-d');

        $fioParts = array_values($parts);
        $lastName = $fioParts[0];
        $firstName = $fioParts[1];
        $middleName = count($fioParts) > 2 ? $fioParts[2] : null;

        $employee = Employee::find()
            ->where([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $birthDate,
            ])
            ->andFilterWhere(['middle_name' => $middleName])
            ->one();

        if (!$employee) {
            throw new \Exception("Сотрудник не найден в системе");
        }

        return $returnObject ? $employee : true;
    }

    /**
     * Извлекает информацию о проблемном сотруднике из строки
     *
     * @param string $employeesString Строка с сотрудниками
     * @return string Информация о проблемном сотруднике
     */
    protected function extractEmployeeFromError($employeesString)
    {
        $employees = array_map('trim', explode(';', $employeesString));

        foreach ($employees as $employeeStr) {
            try {
                $this->parseEmployee($employeeStr);
            } catch (\Exception $e) {
                if ($e->getMessage() === 'Сотрудник не найден в системе') {
                    return $employeeStr;
                }
            }
        }

        return 'Не удалось определить ФИО';
    }

    /**
     * Обрабатывает тип документа, удаляя указанные форматы в скобках и весь текст после них
     *
     * @param string $type Тип документа, который может содержать (CDA) или (PDF/A-1)
     * @return string Тип документа без указанных форматов и последующего текста
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
     * @param mixed $date Дата в различных форматах
     * @return string Дата в формате Y-m-d
     * @throws \Exception Если неверный формат даты
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

    /**
     * Выводит отчет об импорте в консоль
     *
     * @param array $report Отчет об импорте
     */
    protected function printReport($report)
    {
        $this->stdout("\n=== ОТЧЕТ ОБ ИМПОРТЕ ===\n", BaseConsole::FG_YELLOW);
        $this->stdout("Успешно импортировано: {$report['imported']}\n", BaseConsole::FG_GREEN);
        $this->stdout("Пропущено (дубликаты): {$report['skipped']}\n", BaseConsole::FG_YELLOW);

        if (!empty($report['errors'])) {
            $this->stderr("\nОШИБКИ ИМПОРТА:\n", BaseConsole::FG_RED);
            foreach ($report['errors'] as $id => $error) {
                $this->stderr("- {$id}: {$error}\n", BaseConsole::FG_RED);
            }
            $this->stderr("\nВсего ошибок: " . count($report['errors']) . "\n", BaseConsole::FG_RED);
        } else {
            $this->stdout("\nОшибок нет\n", BaseConsole::FG_GREEN);
        }
    }
}