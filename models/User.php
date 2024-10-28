<?php
namespace app\models;

use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string $password
 * @property string $auth_key
 * @property string $unique_id
 * @property string $telegram_chat_id
 * @property string $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Employee $employee
 */

class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    public $confirm_password;
    public $role;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const SCENARIO_PASSWORD = 'password';

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['username', 'unique'],
            ['username', 'required'],
            ['username', 'string', 'min' => 3],
            ['username', 'trim'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9]+$/', 'message' => 'Используйте только латинские буквы и цифры для имени пользователя.'],

            ['password', 'validatePassword'],
            ['password', 'string', 'min' => 6],
            ['password', 'trim'],
            ['password', 'required', 'on' => self::SCENARIO_PASSWORD],

            ['confirm_password', 'required', 'on' => self::SCENARIO_PASSWORD],
            ['confirm_password', 'string', 'min' => 6],
            ['confirm_password', 'trim'],
            ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли должны совпадать.', 'on' => self::SCENARIO_PASSWORD],

            ['password_hash', 'required'],

            ['email', 'email'],
            ['email', 'unique'],
            ['email', 'trim'],
            ['email', 'required'],

            ['role', 'string'],
            ['role', 'required'],

            ['telegram_chat_id', 'string'],

            ['unique_id', 'string'],
            ['unique_id', 'required'],

            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'username' => 'Имя пользователя',
            'password' => 'Пароль',
            'confirm_password' => 'Подтвердите новый пароль',
            'telegram_chat_id' => 'Телеграм чат',
            'status' => 'Статус',
            'role' => 'Роль',
            'created_at' => 'Запись создана',
            'updated_at' => 'Запись обновлена',
        ];
    }

    /**
     * @param $id
     * @return User|IdentityInterface|null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param $token
     * @param $type
     * @return User|IdentityInterface|null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @param $username
     * @return User|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @param $password
     * @return void
     * @throws Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @return string[]
     */
    public static function getStatusesArray()
    {
        return [
            self::STATUS_ACTIVE => 'Действует',
            self::STATUS_INACTIVE => 'Не действует',
        ];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->status, 'Неизвестный статус');
    }

    /**
     * @return array
     */
    public static function getRolesArray()
    {
        $auth = Yii::$app->authManager;
        return ArrayHelper::map($auth->getRoles(), 'name', 'description');
    }

    /**
     * @param $role
     * @return array
     */
    public static function getRolesList($role = null)
    {
        return ArrayHelper::map(
            AuthItem::find()
                ->where(['type' => 1, 'status' => AuthItem::STATUS_ACTIVE])
                ->orWhere(['name' => $role])
                ->all(),
            'name',
            'description'
        );
    }

    /**
     * @return string
     */
    public function getRoleName()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return !empty($roles) ? reset($roles)->description : 'Нет роли';
    }

    /**
     * @return int|string|null
     */
    public function getCurrentRole()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return !empty($roles) ? key($roles) : null;
    }

    /**
     * @param $insert
     * @param $changedAttributes
     * @return void
     * @throws \Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $auth = Yii::$app->authManager;

        if (!empty($this->role)) {
            $auth->revokeAll($this->id);
            $auth->assign($auth->getRole($this->role), $this->id);
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['user_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return self::find()
            ->select('status')
            ->distinct()
            ->where(['status' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]])
            ->column();
    }

    /**
     * @return array
     */
    public static function getAvailableRoles()
    {
        return (new Query())
            ->select('item_name')
            ->from('auth_assignment')
            ->distinct()
            ->where(['user_id' => self::find()->select('id')->column()])
            ->column();
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['user_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getEmployeeFullName()
    {
        return $this->employee ? $this->employee->getFullName() : $this->username;
    }

    /**
     * @return string
     */
    public function getEmployeeFullNameAndPosition()
    {
        return $this->employee ? $this->employee->getFullNameAndPosition() : $this->username;
    }

    /**
     * @return bool
     */
    public function setRole()
    {
        return (bool)$this->role = $this->getCurrentRole();
    }
}
