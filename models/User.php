<?php
namespace app\models;

use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
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
 * @property string $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
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

            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
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
     * @return int|mixed|string|null
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
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_INACTIVE => 'Неактивный',
        ];
    }

    /**
     * @return mixed
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

        $auth->revokeAll($this->id);

        if (!empty($this->role)) {
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
        return (new \yii\db\Query())
            ->select('item_name')
            ->from('auth_assignment')
            ->distinct()
            ->where(['user_id' => self::find()->select('id')->column()])
            ->column();
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if(!$this->role) {
                $this->role = $this->getCurrentRole();
            }
            return true;
        }
        return false;
    }
}
