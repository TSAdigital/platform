<?php

namespace app\commands;

use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use moonland\phpexcel\Excel;
use app\models\User;
use app\models\Employee;
use app\models\Position;

class ImportController extends Controller
{
    private const REQUIRED_COLUMNS = [
        'Фамилия',
        'Имя',
        'Отчество',
        'Дата рождения',
        'Должность',
        'Почта',
    ];

    private const DEFAULT_EMAIL_DOMAIN = '@mail.local';

    private $importedCount = 0;
    private $skippedCount = 0;

    /**
     * Основное действие для импорта пользователей из Excel файла
     *
     * Выполняет:
     * 1. Загрузку и проверку файла
     * 2. Создание пользователей и сотрудников
     * 3. Сохранение учетных данных в файл
     *
     * @return int Код завершения (ExitCode::OK при успехе)
     */
    public function actionInit(): int
    {
        $this->stdout("Начало импорта пользователей...\n", BaseConsole::FG_YELLOW);

        try {
            $filePath = Yii::getAlias('@app/web/import/import.xlsx');

            if (!file_exists($filePath)) {
                throw new Exception("Файл импорта не найден: {$filePath}");
            }

            $data = Excel::import($filePath);
            if (empty($data)) {
                throw new Exception("Файл импорта пуст или не может быть прочитан");
            }

            $this->checkColumns($data[0]);

            $users = [];
            $transaction = Yii::$app->db->beginTransaction();

            /** @var array $data */
            foreach ($data as $index => $row) {
                $rowNumber = $index + 1;

                try {
                    $userData = $this->prepareUserData($row);

                    $username = $this->generateUsername(
                        $userData['lastName'],
                        $userData['firstName'],
                        $userData['middleName']
                    );
                    $email = $this->generateEmail(
                        $userData['email'],
                        substr($userData['firstName'], 0, 2),
                        $userData['lastName']
                    );

                    if (User::find()->where(['or', ['username' => $username], ['email' => $email]])->exists()) {
                        $this->skippedCount++;
                        continue;
                    }

                    $user = $this->createUser($userData);
                    $this->createEmployee($user, $userData);

                    $users[] = [
                        'name' => $userData['fullName'],
                        'username' => $user->username,
                        'email' => $user->email,
                        'password' => $userData['password'],
                    ];

                    $this->importedCount++;
                } catch (Exception $e) {
                    $this->stdout("ошибка\n", BaseConsole::FG_RED);
                    $this->stdout("Ошибка в строке {$rowNumber}: {$e->getMessage()}\n", BaseConsole::FG_RED);
                    $transaction->rollBack();
                    return ExitCode::UNSPECIFIED_ERROR;
                }
            }

            $transaction->commit();

            if ($this->importedCount > 0) {
                $this->saveUsersToFile($users);
                $this->stdout("Файл с учетными данными создан: @app/web/export/users.txt\n", BaseConsole::FG_YELLOW);
            }

            $this->stdout("\nИмпорт завершен!\n", BaseConsole::FG_GREEN);
            $this->stdout("Всего обработано записей: " . (count($data)) . "\n", BaseConsole::FG_YELLOW);
            $this->stdout("Успешно импортировано: {$this->importedCount}\n", BaseConsole::FG_GREEN);
            $this->stdout("Пропущено (уже существует): {$this->skippedCount}\n", BaseConsole::FG_YELLOW);

            return ExitCode::OK;
        } catch (Exception $e) {
            $this->stdout("\nОшибка импорта: {$e->getMessage()}\n", BaseConsole::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Проверяет наличие обязательных колонок в данных
     *
     * @param array $firstRow Первая строка данных из файла
     * @throws Exception Если отсутствуют обязательные колонки
     */
    private function checkColumns(array $firstRow): void
    {
        $missingColumns = array_diff(
            self::REQUIRED_COLUMNS,
            array_keys($firstRow)
        );

        if (!empty($missingColumns)) {
            throw new Exception(sprintf(
                "Отсутствуют обязательные колонки: %s",
                implode(', ', $missingColumns)
            ));
        }
    }

    /**
     * Подготавливает данные пользователя из строки файла
     *
     * @param array $row Строка данных из файла
     * @return array Подготовленные данные пользователя
     * @throws Exception Если обязательные поля не заполнены или неверный формат даты
     */
    private function prepareUserData(array $row): array
    {
        $lastName = trim(ArrayHelper::getValue($row, 'Фамилия', ''));
        $firstName = trim(ArrayHelper::getValue($row, 'Имя', ''));
        $middleName = trim(ArrayHelper::getValue($row, 'Отчество', ''));
        $birthdate = trim(ArrayHelper::getValue($row, 'Дата рождения', ''));
        $position = trim(ArrayHelper::getValue($row, 'Должность', ''));
        $email = trim(ArrayHelper::getValue($row, 'Почта', ''));

        if (empty($lastName) || empty($firstName) || empty($birthdate) || empty($position)) {
            throw new Exception("Обязательные поля не заполнены");
        }

        $birthdateTimestamp = strtotime($birthdate);
        if (!$birthdateTimestamp) {
            throw new Exception("Неверный формат даты рождения");
        }

        return [
            'lastName' => $lastName,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'fullName' => implode(' ', array_filter([$lastName, $firstName, $middleName])),
            'birthdate' => date('d.m.Y', $birthdateTimestamp),
            'position' => $position,
            'email' => $email,
            'password' => $this->generatePassword(),
        ];
    }

    /**
     * Создает нового пользователя в системе
     *
     * @param array $userData Данные пользователя
     * @return User Созданный пользователь
     * @throws Exception Если не удалось сохранить пользователя
     */
    private function createUser(array $userData): User
    {
        $user = new User();
        $user->username = $this->generateUsername(
            $userData['lastName'],
            $userData['firstName'],
            $userData['middleName']
        );
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->password_hash = Yii::$app->security->generatePasswordHash($userData['password']);
        $user->email = $this->generateEmail($userData['email'], substr($userData['firstName'], 0, 2), $userData['lastName']);
        $user->unique_id = Yii::$app->security->generateRandomString(12);
        $user->status = User::STATUS_ACTIVE;
        $user->role = 'user';

        if (!$user->save()) {
            throw new Exception("Ошибка сохранения пользователя: " . implode(', ', $user->getFirstErrors()));
        }

        return $user;
    }

    /**
     * Создает запись сотрудника на основе данных пользователя
     *
     * @param User $user Объект пользователя
     * @param array $userData Данные пользователя
     * @throws Exception Если не удалось сохранить сотрудника
     */
    private function createEmployee(User $user, array $userData): void
    {
        $employee = new Employee();
        $employee->last_name = $userData['lastName'];
        $employee->first_name = $userData['firstName'];
        $employee->middle_name = $userData['middleName'];
        $employee->birth_date = $userData['birthdate'];
        $employee->position_id = $this->getPositionId($userData['position']);
        $employee->user_id = $user->id;
        $employee->status = Employee::STATUS_ACTIVE;

        if (!$employee->save()) {
            throw new Exception("Ошибка сохранения сотрудника: " . implode(', ', $employee->getFirstErrors()));
        }
    }

    /**
     * Сохраняет учетные данные пользователей в файл
     *
     * Формат файла: ФИО/логин/пароль/email
     *
     * @param array $users Массив с данными пользователей
     */
    private function saveUsersToFile(array $users): void
    {
        $content = "";
        foreach ($users as $user) {
            $content .= "{$user['name']}/{$user['username']}/{$user['password']}/{$user['email']}\n";
        }

        $dirPath = Yii::getAlias('@app/web/export');
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        $filePath = "{$dirPath}/users.txt";
        file_put_contents($filePath, $content);
    }

    /**
     * Генерирует уникальное имя пользователя на основе ФИО
     *
     * @param string $lastName Фамилия
     * @param string $firstName Имя
     * @param string $middleName Отчество
     * @return string Сгенерированное имя пользователя
     */
    private function generateUsername(string $lastName, string $firstName, string $middleName): string
    {
        $transliteratedLastName = $this->customTransliterate($lastName);
        $transliteratedFirstName = $this->customTransliterate($firstName);
        $transliteratedMiddleName = $this->customTransliterate($middleName);

        return $transliteratedLastName
            . $this->getLeadingUppercase($transliteratedFirstName)
            . $this->getLeadingUppercase($transliteratedMiddleName);
    }

    /**
     * Генерирует email адрес на основе ФИО
     *
     * Если email указан в файле и валиден - использует его,
     * иначе генерирует в формате имя.фамилия@mail.local
     *
     * @param string $email Email из файла (может быть пустым)
     * @param string $firstName Имя
     * @param string $lastName Фамилия
     * @return string Email адрес
     */
    private function generateEmail(string $email, string $firstName, string $lastName): string
    {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $transliteratedFirstName = strtolower($this->customTransliterate($firstName));
        $transliteratedLastName = strtolower($this->customTransliterate($lastName));

        return "{$transliteratedFirstName}.{$transliteratedLastName}" . self::DEFAULT_EMAIL_DOMAIN;
    }

    /**
     * Получает ID должности, создает новую при необходимости
     *
     * @param string $positionName Название должности
     * @return int ID должности
     * @throws Exception Если не удалось создать должность
     */
    private function getPositionId(string $positionName): int
    {
        $position = Position::findOne(['name' => $positionName]);

        if ($position) {
            return $position->id;
        }

        $position = new Position();
        $position->name = $positionName;
        $position->status = Position::STATUS_ACTIVE;

        if (!$position->save()) {
            throw new Exception("Ошибка сохранения должности: " . implode(', ', $position->getFirstErrors()));
        }

        return $position->id;
    }

    /**
     * Транслитерирует русские символы в английские
     *
     * @param string $string Исходная строка
     * @return string Транслитерированная строка
     */
    private function customTransliterate(string $string): string
    {
        $transliterationMap = [
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];

        $transliterated = '';
        $length = mb_strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($string, $i, 1);
            $transliterated .= $transliterationMap[$char] ?? $char;
        }

        $specialCases = ['Sh', 'Ch', 'Shch', 'Zh', 'Kh', 'Ts', 'Yu', 'Ya'];
        foreach ($specialCases as $case) {
            if (strpos($transliterated, $case) === 0) {
                return strtoupper($case) . substr($transliterated, strlen($case));
            }
        }

        return ucfirst($transliterated);
    }

    /**
     * Извлекает заглавные буквы из начала строки
     *
     * @param string $string Исходная строка
     * @return string Заглавные буквы из начала строки
     */
    private function getLeadingUppercase(string $string): string
    {
        $result = '';
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            if (ctype_upper($string[$i])) {
                $result .= $string[$i];
            } else {
                break;
            }
        }

        return $result;
    }

    /**
     * Генерирует случайный пароль
     *
     * Формат: 6 цифр + 2 буквы в случайном порядке
     *
     * @return string Сгенерированный пароль
     */
    private function generatePassword(): string
    {
        $numbers = '123456789';
        $letters = 'abcdefghijklmnopqrstuvwxyz';

        $randomNumbers = '';
        for ($i = 0; $i < 6; $i++) {
            $randomNumbers .= $numbers[random_int(0, strlen($numbers) - 1)];
        }

        $randomLetters = '';
        for ($i = 0; $i < 2; $i++) {
            $randomLetters .= $letters[random_int(0, strlen($letters) - 1)];
        }

        return str_shuffle($randomNumbers . $randomLetters);
    }
}