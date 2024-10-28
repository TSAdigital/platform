<?php

namespace app\commands;

use app\models\Employee;
use app\models\Position;
use app\models\User;
use moonland\phpexcel\Excel;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\BaseConsole;

class ImportController extends Controller
{

    /**
     * @return void
     * @throws Exception
     */
    public function actionInit()
    {
        $url = Yii::getAlias('@app/web/import/import.xlsx');
        $data = (array) Excel::import($url);

        $users = [];

        foreach ($data as $value) {
            $lastName = trim($value['Фамилия']);
            $firstName = trim($value['Имя']);
            $middleName = trim($value['Отчество']);
            $birthdate = trim($value['Дата рождения']);
            $position = trim($value['Должность']);
            $email = trim($value['Почта']);
            $password = $this->generatePassword();

            $user = new User();
            $user->username = $this->customTransliterate($lastName) . $this->getLeadingUppercase($this->customTransliterate($firstName)) . $this->getLeadingUppercase($this->customTransliterate($middleName));
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
            $user->email = !empty($email) ? $email : strtolower($this->getLeadingUppercase($this->customTransliterate($firstName)) . '.'. $this->customTransliterate($lastName) . '@mail.local');
            $user->unique_id = Yii::$app->security->generateRandomString(12);
            $user->status = User::STATUS_ACTIVE;
            $user->role = 'user';

            if ($user->save()) {
                $users[] = [
                    'name' => implode(' ', [$lastName, $firstName, $middleName]),
                    'username' => $user->username,
                    'email' => $user->email,
                    'password' => $password
                ];

                $employee = new Employee();
                $employee->last_name = $lastName;
                $employee->first_name = $firstName;
                $employee->middle_name = $middleName;
                $employee->birth_date = date('d.m.Y', strtotime($birthdate));
                $employee->position_id = $this->getPositionId($position);
                $employee->user_id = $user->id;
                $employee->status = Employee::STATUS_ACTIVE;
                $employee->save();
            }

            $content = "";

            foreach ($users as $item) {
                $content .= "{$item['name']}/{$item['username']}/{$item['password']}/{$item['email']}\n";
            }

            $filePath = Yii::getAlias('@app/web/export/users.txt');

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            file_put_contents($filePath, $content);
        }
        $this->stdout("Импорт пользователей завершен!\n", BaseConsole::FG_GREEN);
    }

    /**
     * @param $position
     * @return false|int
     * @throws \yii\db\Exception
     */
    function getPositionId($position)
    {
        $model = Position::findOne(['name' => $position]);

        if($model){
            return $model->id;
        } else {
            $model = new Position();
            $model->name = $position;
            $model->status = Position::STATUS_ACTIVE;
            if ($model->save()) {
                return $model->id;
            }
        }
        return false;
    }

    /**
     * @param $surname
     * @return string
     */
    function customTransliterate($surname)
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
        $length = mb_strlen($surname);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($surname, $i, 1);

            if (array_key_exists($char, $transliterationMap)) {
                $transliterated .= $transliterationMap[$char];
            } else {
                $transliterated .= $char;
            }
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
     * @param $string
     * @return string
     */
    function getLeadingUppercase($string)
    {
        $result = '';

        for ($i = 0; $i < strlen($string); $i++) {
            if (ctype_upper($string[$i])) {
                $result .= $string[$i];
            } else {
                break;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    function generatePassword() {
        $numbers = '123456789';
        $letters = 'abcdefghijklmnopqrstuvwxyz';

        $randomNumbers = '';
        for ($i = 0; $i < 6; $i++) {
            $randomNumbers .= $numbers[rand(0, strlen($numbers) - 1)];
        }

        $randomLetters = '';
        for ($i = 0; $i < 2; $i++) {
            $randomLetters .= $letters[rand(0, strlen($letters) - 1)];
        }

        $password = str_shuffle($randomNumbers . $randomLetters);

        return $password;
    }
}