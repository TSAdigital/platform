<?php
namespace app\commands;

use app\models\User;
use Faker\Factory;
use yii\console\Controller;
use Yii;

class UserController extends Controller
{
    public function actionCreate($count = 1000)
    {
        $faker = Factory::create();

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        $roleNames = array_keys($roles);

        $success = 0;

        for ($i = 1; $i <= $count; $i++) {
            $role = $roleNames[array_rand($roleNames)];

            if ($this->createUser($faker, $role)) {
                $success++;
            }

        }

        echo "Успешно создано пользователей - $success \n";
    }

    protected function createUser($faker, $role)
    {
        $user = new User();
        $user->username = $faker->userName;
        $user->email = $faker->unique()->safeEmail;
        $user->password_hash = Yii::$app->security->generatePasswordHash('12345678');
        $user->role = $role;
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->created_at = time();
        $user->updated_at = time();

        if (!$user->save()) {
            echo "Ошибка при создании пользователя: " . implode(", ", $user->getErrorSummary(true)) . "\n";
            return false;
        }
        return true;
    }
}
